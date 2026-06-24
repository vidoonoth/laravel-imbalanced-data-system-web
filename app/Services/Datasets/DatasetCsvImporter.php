<?php

namespace App\Services\Datasets;

use App\Models\Dataset;
use App\Models\DatasetImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use SplFileObject;
use Throwable;

class DatasetCsvImporter
{
    /**
     * @param  array{
     *     host?: string|null,
     *     path?: string|null,
     *     filename?: string|null,
     *     size_bytes?: int|null,
     *     last_modified_at?: mixed
     * }  $source
     * @return array{status: string, filename: string|null, rows_imported: int, import_id: int|null}
     */
    public function importFile(string $localPath, array $source, array $options = []): array
    {
        if (! is_file($localPath)) {
            throw new RuntimeException("File CSV tidak ditemukan: {$localPath}");
        }

        $sourceHost = (string) ($source['host'] ?? 'local');
        $sourcePath = (string) ($source['path'] ?? $localPath);
        $sourceFilename = $source['filename'] ?? basename($sourcePath);
        $fingerprint = self::sourceFingerprint($sourceHost, $sourcePath);
        $force = (bool) ($options['force'] ?? false);

        $existingImport = DatasetImport::query()
            ->where('source_fingerprint', $fingerprint)
            ->first();

        if ($existingImport?->status === DatasetImport::STATUS_COMPLETED && ! $force) {
            return [
                'status' => 'skipped',
                'filename' => $existingImport->source_filename,
                'rows_imported' => $existingImport->rows_imported,
                'import_id' => $existingImport->id,
            ];
        }

        $import = DatasetImport::query()->updateOrCreate(
            ['source_fingerprint' => $fingerprint],
            [
                'source_host' => $sourceHost,
                'source_path' => $sourcePath,
                'source_filename' => $sourceFilename,
                'size_bytes' => $source['size_bytes'] ?? filesize($localPath),
                'last_modified_at' => $source['last_modified_at'] ?? null,
                'checksum_sha256' => hash_file('sha256', $localPath) ?: null,
                'status' => DatasetImport::STATUS_PROCESSING,
                'rows_imported' => 0,
                'error_message' => null,
                'started_at' => now(),
                'finished_at' => null,
            ]
        );

        try {
            $rowsImported = DB::transaction(function () use ($import, $localPath, $options): int {
                $import->datasets()->delete();

                $rowsImported = $this->storeRows($import, $localPath, $options);

                $import->forceFill([
                    'status' => DatasetImport::STATUS_COMPLETED,
                    'rows_imported' => $rowsImported,
                    'error_message' => null,
                    'finished_at' => now(),
                ])->save();

                return $rowsImported;
            });
        } catch (Throwable $exception) {
            $import->forceFill([
                'status' => DatasetImport::STATUS_FAILED,
                'error_message' => Str::limit($exception->getMessage(), 1000),
                'finished_at' => now(),
            ])->save();

            throw $exception;
        }

        return [
            'status' => 'imported',
            'filename' => $import->source_filename,
            'rows_imported' => $rowsImported,
            'import_id' => $import->id,
        ];
    }

    public static function sourceFingerprint(string $sourceHost, string $sourcePath): string
    {
        return hash('sha256', "{$sourceHost}|{$sourcePath}");
    }

    public function wasCompleted(string $sourceHost, string $sourcePath): ?DatasetImport
    {
        return DatasetImport::query()
            ->where('source_fingerprint', self::sourceFingerprint($sourceHost, $sourcePath))
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->first();
    }

    private function storeRows(DatasetImport $import, string $localPath, array $options): int
    {
        $file = new SplFileObject($localPath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->delimiter($options), '"', '\\');

        $hasHeader = (bool) ($options['has_header'] ?? config('services.vps_csv.has_header', true));
        $header = null;
        $rowsImported = 0;
        $rowNumber = 0;
        $buffer = [];
        $now = now();

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            $rowNumber++;

            if ($this->isBlankRow($row)) {
                continue;
            }

            if ($hasHeader && $header === null) {
                $header = $this->normalizeHeaders($row);

                continue;
            }

            $payload = $hasHeader
                ? $this->rowWithHeader($header ?? [], $row)
                : $this->rowWithGeneratedColumns($row);
            $encodedPayload = $this->encodePayload($payload);

            $buffer[] = [
                'dataset_import_id' => $import->id,
                'row_number' => $rowNumber,
                'row_hash' => hash('sha256', $encodedPayload),
                'payload' => $encodedPayload,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rowsImported++;

            if (count($buffer) >= 500) {
                Dataset::query()->insert($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            Dataset::query()->insert($buffer);
        }

        return $rowsImported;
    }

    private function delimiter(array $options): string
    {
        $delimiter = (string) ($options['delimiter'] ?? config('services.vps_csv.delimiter', ','));

        if ($delimiter === '') {
            return ',';
        }

        return $delimiter === '\t' ? "\t" : $delimiter[0];
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->normalizeValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeaders(array $row): array
    {
        $headers = [];
        $seen = [];

        foreach ($row as $index => $value) {
            $name = $this->normalizeValue($value) ?? '';
            $name = preg_replace('/^\xEF\xBB\xBF/', '', $name) ?? $name;
            $name = $name !== '' ? $name : 'column_'.($index + 1);
            $baseName = $name;
            $counter = 2;

            while (isset($seen[$name])) {
                $name = "{$baseName}_{$counter}";
                $counter++;
            }

            $seen[$name] = true;
            $headers[] = $name;
        }

        return $headers;
    }

    private function rowWithHeader(array $header, array $row): array
    {
        $payload = [];
        $columnCount = max(count($header), count($row));

        for ($index = 0; $index < $columnCount; $index++) {
            $key = $header[$index] ?? 'extra_column_'.($index + 1);
            $payload[$key] = $this->normalizeValue($row[$index] ?? null);
        }

        return $payload;
    }

    private function rowWithGeneratedColumns(array $row): array
    {
        $payload = [];

        foreach ($row as $index => $value) {
            $payload['column_'.($index + 1)] = $this->normalizeValue($value);
        }

        return $payload;
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }

    /**
     * @throws JsonException
     */
    private function encodePayload(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    }
}
