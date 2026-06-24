<?php

namespace App\Console\Commands;

use App\Services\Datasets\VpsCsvDatasetImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportVpsDatasets extends Command
{
    protected $signature = 'datasets:import-vps
        {--limit= : Maksimal file CSV yang diproses}
        {--dry-run : Cek file remote tanpa mengunduh dan menyimpan ke database}
        {--force : Import ulang file yang sebelumnya sudah completed}';

    protected $description = 'Mengambil file CSV dari VPS dan menyimpannya ke tabel datasets.';

    public function handle(VpsCsvDatasetImporter $importer): int
    {
        if (! (bool) config('services.vps_csv.enabled', false)) {
            $this->warn('VPS_CSV_ENABLED=false, import dataset dari VPS dimatikan.');

            return self::SUCCESS;
        }

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        try {
            $summary = $importer->import(
                limit: $limit,
                dryRun: (bool) $this->option('dry-run'),
                force: (bool) $this->option('force'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("File ditemukan: {$summary['found']}");
        $this->line("Imported: {$summary['imported']} | Skipped: {$summary['skipped']} | Failed: {$summary['failed']}");

        foreach ($summary['files'] as $file) {
            $filename = $file['filename'] ?? '-';
            $status = $file['status'] ?? 'unknown';
            $rows = array_key_exists('rows_imported', $file) ? " ({$file['rows_imported']} rows)" : '';
            $reason = array_key_exists('reason', $file) ? " - {$file['reason']}" : '';
            $error = array_key_exists('error', $file) ? " - {$file['error']}" : '';

            $this->line("{$status}: {$filename}{$rows}{$reason}{$error}");
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
