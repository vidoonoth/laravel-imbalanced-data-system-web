<?php

namespace App\Console\Commands;

use App\Models\DetectionResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillDetectionFields extends Command
{
    protected $signature = 'detection:backfill-fields
        {--dry-run : Simulasi tanpa menyimpan}
        {--limit= : Batasi jumlah record yang diproses}';

    protected $description = 'Backfill event_name, disposition, action dari dataset payload';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info('Memulai backfill detection fields...');
        $this->newLine();

        $query = DetectionResult::query()
            ->whereNotNull('dataset_id')
            ->with('dataset');

        if ($limit) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada record untuk diproses.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} detection results dengan dataset.");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->withProgressBar($total, function ($bar) use ($query, $dryRun, &$updated, &$skipped, &$errors) {
            $query->chunk(100, function ($results) use ($dryRun, &$updated, &$skipped, &$errors, $bar) {
                foreach ($results as $result) {
                    $bar->advance();

                    if (!$result->dataset || !$result->dataset->payload) {
                        $skipped++;
                        continue;
                    }

                    $payload = $result->dataset->payload;
                    $updates = [];

                    $fieldMapping = [
                        'update_time' => 'update_time',
                        'sn' => 'sn',
                        'event_type' => 'event_name',
                        'event_name' => 'event_name',
                        'application' => 'disposition',
                        'disposition' => 'disposition',
                        'action' => 'action',
                        'log_type' => 'log_type',
                        'severity' => 'priority',
                        'priority' => 'priority',
                        'message' => 'log',
                        'log' => 'log',
                        'src_country' => 'geo_src',
                        'geo_src' => 'geo_src',
                        'dst_country' => 'geo_dst',
                        'geo_dst' => 'geo_dst',
                        'policy_name' => 'policy',
                        'policy' => 'policy',
                        'source_interface' => 'source_interface',
                        'destination_interface' => 'destination_interface',
                        'pckt_len' => 'pckt_len',
                        'ttl' => 'ttl',
                        'sent_bytes' => 'sent_bytes',
                        'rcvd_bytes' => 'rcvd_bytes',
                    ];

                    foreach ($fieldMapping as $payloadKey => $dbColumn) {
                        $value = $this->getPayloadValue($payload, $payloadKey);
                        
                        if ($value !== null && $result->{$dbColumn} === null) {
                            $updates[$dbColumn] = $value;
                        }
                    }

                    if (empty($updates)) {
                        $skipped++;
                        continue;
                    }

                    if (!$dryRun) {
                        try {
                            $result->update($updates);
                            $updated++;
                        } catch (\Throwable $e) {
                            $errors++;
                            $this->newLine();
                            $this->error("Error ID {$result->id}: {$e->getMessage()}");
                        }
                    } else {
                        $updated++;
                    }
                }
            });
        });

        $this->newLine(2);
        $this->info('=== Ringkasan Backfill ===');
        $this->line("Total diproses: {$total}");
        $this->line("Berhasil update: {$updated}");
        $this->line("Dilewati: {$skipped}");
        $this->line("Error: {$errors}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('Dry run - tidak ada data yang disimpan');
        }

        $this->newLine();
        $this->info('✓ Backfill selesai.');

        return self::SUCCESS;
    }

    private function getPayloadValue(array $payload, string $key)
    {
        $normalizedPayload = array_change_key_case($payload, CASE_LOWER);
        $normalizedKey = strtolower($key);
        
        $value = $normalizedPayload[$normalizedKey] ?? null;
        
        if ($value === null || $value === '' || $value === 'unknown') {
            return null;
        }
        
        return is_scalar($value) ? $value : null;
    }
}
