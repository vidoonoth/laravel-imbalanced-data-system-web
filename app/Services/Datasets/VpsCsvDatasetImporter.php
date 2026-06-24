<?php

namespace App\Services\Datasets;

use Carbon\CarbonImmutable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class VpsCsvDatasetImporter
{
    public function __construct(
        private readonly DatasetCsvImporter $csvImporter,
        private readonly Filesystem $files,
    ) {}

    /**
     * @return array{found: int, imported: int, skipped: int, failed: int, files: array<int, array<string, mixed>>}
     */
    public function import(?int $limit = null, bool $dryRun = false, bool $force = false): array
    {
        $files = $this->listCsvFiles();
        $maxFiles = $limit ?? (int) config('services.vps_csv.max_files', 10);

        if ($maxFiles > 0) {
            $files = array_slice($files, 0, $maxFiles);
        }

        $summary = [
            'found' => count($files),
            'imported' => 0,
            'skipped' => 0,
            'failed' => 0,
            'files' => [],
        ];

        foreach ($files as $file) {
            $completedImport = $this->csvImporter->wasCompleted($this->host(), $file['path']);

            if ($completedImport !== null && ! $force) {
                $summary['skipped']++;
                $summary['files'][] = [
                    'status' => 'skipped',
                    'filename' => $file['filename'],
                    'rows_imported' => $completedImport->rows_imported,
                    'reason' => 'already_imported',
                ];

                continue;
            }

            if ($dryRun) {
                $summary['files'][] = [
                    'status' => 'pending',
                    'filename' => $file['filename'],
                    'size_bytes' => $file['size_bytes'],
                    'path' => $file['path'],
                ];

                continue;
            }

            $localPath = null;

            try {
                $localPath = $this->downloadFile($file);

                $result = $this->csvImporter->importFile($localPath, [
                    'host' => $this->host(),
                    'path' => $file['path'],
                    'filename' => $file['filename'],
                    'size_bytes' => $file['size_bytes'],
                    'last_modified_at' => $file['last_modified_at'],
                ], [
                    'force' => $force,
                    'has_header' => config('services.vps_csv.has_header', true),
                    'delimiter' => config('services.vps_csv.delimiter', ','),
                ]);

                if ($result['status'] === 'imported') {
                    $summary['imported']++;
                } else {
                    $summary['skipped']++;
                }

                $summary['files'][] = $result + ['path' => $file['path']];

                if ((bool) config('services.vps_csv.delete_after_processing', false)) {
                    $this->deleteRemoteFile($file['path']);
                }
            } catch (Throwable $exception) {
                $summary['failed']++;
                $summary['files'][] = [
                    'status' => 'failed',
                    'filename' => $file['filename'],
                    'path' => $file['path'],
                    'error' => $exception->getMessage(),
                ];
            } finally {
                if (
                    $localPath !== null
                    && ! (bool) config('services.vps_csv.keep_local_copy', false)
                    && $this->files->exists($localPath)
                ) {
                    $this->files->delete($localPath);
                }
            }
        }

        return $summary;
    }

    /**
     * @return array<int, array{filename: string, path: string, size_bytes: int|null, last_modified_at: CarbonImmutable|null}>
     */
    public function listCsvFiles(): array
    {
        $this->ensureConfigured();

        $remoteCommand = sprintf(
            'find %s -maxdepth 1 -type f -name %s -printf %s | sort',
            $this->posixQuote($this->sourceDir()),
            $this->posixQuote('*.csv'),
            $this->posixQuote("%f\\t%p\\t%s\\t%T@\\n"),
        );

        $output = $this->runProcess($this->sshArgs($remoteCommand), 'Gagal membaca daftar file CSV dari VPS.');
        $lines = preg_split('/\r\n|\r|\n/', trim($output)) ?: [];
        $files = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $parts = explode("\t", $line);

            if (count($parts) < 4) {
                continue;
            }

            [$filename, $path, $sizeBytes, $lastModifiedTimestamp] = $parts;

            $files[] = [
                'filename' => $filename,
                'path' => $path,
                'size_bytes' => is_numeric($sizeBytes) ? (int) $sizeBytes : null,
                'last_modified_at' => is_numeric($lastModifiedTimestamp)
                    ? CarbonImmutable::createFromTimestampUTC((int) floor((float) $lastModifiedTimestamp))
                    : null,
            ];
        }

        return $files;
    }

    private function downloadFile(array $file): string
    {
        $localDir = storage_path('app/private/'.trim((string) config('services.vps_csv.local_dir', 'datasets/vps'), '/\\'));
        $this->files->ensureDirectoryExists($localDir);

        $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $file['filename']) ?: 'dataset.csv';
        $localPath = $localDir.DIRECTORY_SEPARATOR.now()->format('YmdHis').'-'.Str::random(8).'-'.$safeFilename;

        $this->runProcess(
            $this->scpArgs($file['path'], $localPath),
            "Gagal mengunduh {$file['filename']} dari VPS."
        );

        return $localPath;
    }

    private function deleteRemoteFile(string $remotePath): void
    {
        $remoteCommand = sprintf('rm -f -- %s', $this->posixQuote($remotePath));

        $this->runProcess($this->sshArgs($remoteCommand), "Gagal menghapus file remote: {$remotePath}");
    }

    /**
     * @param  array<int, string>  $command
     */
    private function runProcess(array $command, string $errorMessage): string
    {
        $process = new Process($command, base_path(), null, null, (int) config('services.vps_csv.timeout', 120));
        $process->run();

        if (! $process->isSuccessful()) {
            $details = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException($details !== '' ? "{$errorMessage} {$details}" : $errorMessage);
        }

        return $process->getOutput();
    }

    /**
     * @return array<int, string>
     */
    private function sshArgs(string $remoteCommand): array
    {
        return array_merge($this->sshBaseArgs('ssh'), [
            $this->userHost(),
            $remoteCommand,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function scpArgs(string $remotePath, string $localPath): array
    {
        return array_merge($this->sshBaseArgs('scp'), [
            '-P',
            (string) $this->port(),
            "{$this->userHost()}:{$remotePath}",
            $localPath,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function sshBaseArgs(string $binary): array
    {
        $args = [
            $binary,
            '-o',
            'BatchMode=yes',
            '-o',
            'StrictHostKeyChecking='.$this->strictHostKeyChecking(),
            '-o',
            'ConnectTimeout='.(int) config('services.vps_csv.connect_timeout', 15),
        ];

        if ($binary === 'ssh') {
            $args[] = '-p';
            $args[] = (string) $this->port();
        }

        $privateKeyPath = $this->privateKeyPath();

        if ($privateKeyPath !== null) {
            $args[] = '-i';
            $args[] = $privateKeyPath;
        }

        return $args;
    }

    private function privateKeyPath(): ?string
    {
        $privateKeyPath = config('services.vps_csv.private_key_path');

        if (is_string($privateKeyPath) && $privateKeyPath !== '') {
            if (! $this->files->exists($privateKeyPath)) {
                throw new RuntimeException("VPS_PRIVATE_KEY_PATH tidak ditemukan: {$privateKeyPath}");
            }

            return $privateKeyPath;
        }

        $privateKey = config('services.vps_csv.private_key');

        if (! is_string($privateKey) || trim($privateKey) === '') {
            return null;
        }

        $keyDir = storage_path('app/private/keys');
        $this->files->ensureDirectoryExists($keyDir);

        $keyPath = $keyDir.DIRECTORY_SEPARATOR.'vps_csv_key';
        $keyContents = rtrim($privateKey).PHP_EOL;

        if (! $this->files->exists($keyPath) || $this->files->get($keyPath) !== $keyContents) {
            $this->files->put($keyPath, $keyContents);
        }

        $this->hardenPrivateKeyPermissions($keyPath);

        return $keyPath;
    }

    private function hardenPrivateKeyPermissions(string $keyPath): void
    {
        @chmod($keyPath, 0600);

        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $username = getenv('USERNAME');

        if (! is_string($username) || $username === '') {
            return;
        }

        $domain = getenv('USERDOMAIN');
        $user = is_string($domain) && $domain !== '' ? "{$domain}\\{$username}" : $username;
        $windowsKeyPath = str_replace('/', '\\', $keyPath);

        foreach ([
            ['icacls', $windowsKeyPath, '/inheritance:r'],
            ['icacls', $windowsKeyPath, '/grant:r', "{$user}:R"],
        ] as $command) {
            (new Process($command, base_path(), null, null, 10))->run();
        }
    }

    private function ensureConfigured(): void
    {
        foreach (['host', 'username', 'source_dir'] as $key) {
            if (! is_string(config("services.vps_csv.{$key}")) || config("services.vps_csv.{$key}") === '') {
                throw new RuntimeException("Konfigurasi services.vps_csv.{$key} belum diisi.");
            }
        }
    }

    private function userHost(): string
    {
        return $this->username().'@'.$this->host();
    }

    private function host(): string
    {
        return (string) config('services.vps_csv.host');
    }

    private function username(): string
    {
        return (string) config('services.vps_csv.username');
    }

    private function port(): int
    {
        return (int) config('services.vps_csv.port', 22);
    }

    private function sourceDir(): string
    {
        return rtrim((string) config('services.vps_csv.source_dir'), '/');
    }

    private function strictHostKeyChecking(): string
    {
        return (string) config('services.vps_csv.strict_host_key_checking', 'accept-new');
    }

    private function posixQuote(string $value): string
    {
        return "'".str_replace("'", "'\\''", $value)."'";
    }
}
