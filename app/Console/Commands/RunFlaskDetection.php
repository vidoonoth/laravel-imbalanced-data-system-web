<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use App\Services\FlaskDetectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class RunFlaskDetection extends Command
{
    protected $signature = 'detection:run-flask
        {--limit= : Batasi jumlah dataset yang diproses}
        {--batch-size=100 : Jumlah record per batch ke Flask API}
        {--check-health : Cek health Flask API}
        {--dry-run : Simulasi tanpa menyimpan hasil}';

    protected $description = 'Jalankan deteksi malware via Flask API';

    public function handle(FlaskDetectionService $flaskService): int
    {
        if ($this->option('check-health')) {
            return $this->checkHealth($flaskService);
        }

        $this->info('Memulai deteksi via Flask API...');

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $batchSize = max((int) $this->option('batch-size'), 1);
        $dryRun = (bool) $this->option('dry-run');

        if (! $flaskService->isAvailable()) {
            $this->error('Flask API tidak tersedia. Cek apakah Flask app sudah running.');
            $this->line('Jalankan: php artisan detection:run-flask --check-health');

            return self::FAILURE;
        }

        $query = Dataset::query()
            ->whereDoesntHave('detectionResult');

        $totalAvailable = (clone $query)->count();
        $total = $limit ? min($totalAvailable, $limit) : $totalAvailable;

        if ($total === 0) {
            $this->info('Tidak ada dataset baru untuk dideteksi.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} dataset untuk dideteksi.");

        $processed = 0;
        $malware = 0;
        $normal = 0;
        $errors = 0;
        $remaining = $limit;
        $chunkSize = $limit ? min($batchSize, $limit) : $batchSize;

        $this->withProgressBar($total, function ($bar) use ($query, $chunkSize, $flaskService, &$processed, &$malware, &$normal, &$errors, &$remaining, $dryRun) {
            $query->chunkById($chunkSize, function (Collection $datasets) use ($flaskService, &$processed, &$malware, &$normal, &$errors, &$remaining, $bar, $dryRun) {
                if ($remaining !== null) {
                    $datasets = $datasets->take($remaining)->values();
                }

                if ($datasets->isEmpty()) {
                    return false;
                }

                try {
                    $results = $flaskService->detectBatch($this->recordsForDetection($datasets));
                    $datasetIds = $datasets->pluck('id')->values();

                    foreach ($results as $index => $result) {
                        $datasetId = $result['_dataset_id'] ?? $datasetIds->get($index);

                        if (! $datasetId) {
                            continue;
                        }

                        if ((int) ($result['prediction'] ?? 0) === 1) {
                            $malware++;
                        } else {
                            $normal++;
                        }

                        if (! $dryRun) {
                            DB::table('detection_results')->updateOrInsert(
                                ['dataset_id' => $datasetId],
                                $this->detectionRow($datasetId, $result)
                            );
                        }
                    }

                    $processed += count($results);
                    $bar->advance(count($results));
                } catch (Throwable $e) {
                    $errors++;
                    $this->error("Error: {$e->getMessage()}");
                    $bar->advance($datasets->count());
                }

                if ($remaining !== null) {
                    $remaining -= $datasets->count();

                    return $remaining > 0;
                }

                return true;
            }, 'id');
        });

        $this->newLine(2);
        $this->info('=== Ringkasan Deteksi ===');
        $this->line("Total diproses: {$processed}");
        $this->line("Malware: {$malware}");
        $this->line("Normal: {$normal}");
        $this->line("Error batch: {$errors}");

        if ($dryRun) {
            $this->warn('Dry run - tidak ada data yang disimpan');
        }

        if ($errors > 0) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('[OK] Deteksi selesai.');

        return self::SUCCESS;
    }

    private function checkHealth(FlaskDetectionService $flaskService): int
    {
        $this->info('Memeriksa Flask API...');
        $this->newLine();

        $health = $flaskService->checkHealth();

        $this->table(
            ['Check', 'Status'],
            [
                ['Flask API Status', $health['status'] ?? 'unknown'],
                ['Model Loaded', ($health['model_loaded'] ?? false) ? 'Yes' : 'No'],
            ]
        );

        if (isset($health['error'])) {
            $this->newLine();
            $this->error("Error: {$health['error']}");
        }

        $this->newLine();

        if (($health['status'] ?? '') === 'healthy') {
            $this->info('[OK] Flask API siap digunakan.');

            return self::SUCCESS;
        }

        $this->error('[ERROR] Flask API tidak siap.');

        return self::FAILURE;
    }

    private function recordsForDetection(Collection $datasets): array
    {
        return $datasets
            ->map(fn (Dataset $dataset) => array_merge(
                $dataset->payload,
                [
                    '_dataset_id' => $dataset->id,
                    '_row_number' => $dataset->row_number,
                ]
            ))
            ->values()
            ->toArray();
    }

    private function detectionRow(int $datasetId, array $result): array
    {
        $row = [
            'dataset_id' => $datasetId,
            'row_index' => $result['_row_number'] ?? 0,
            'prediction' => $result['prediction'] ?? null,
            'prediction_label' => $result['prediction_label'] ?? null,
            'confidence' => $result['confidence'] ?? null,
            'probability_normal' => $result['probability_normal'] ?? null,
            'probability_attack' => $result['probability_attack'] ?? null,
            'source_ip' => $result['source_ip'] ?? null,
            'destination_ip' => $result['destination_ip'] ?? null,
            'source_port' => $result['source_port'] ?? null,
            'destination_port' => $result['destination_port'] ?? null,
            'protocol' => $result['protocol'] ?? null,
            'detected_at' => $result['timestamp'] ?? now(),
            'raw_record' => json_encode($result),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach ($this->optionalFieldMap() as $resultKey => $dbColumn) {
            if (isset($result[$resultKey])) {
                $row[$dbColumn] = $result[$resultKey];
            }
        }

        return $row;
    }

    /**
     * @return array<string, string>
     */
    private function optionalFieldMap(): array
    {
        return [
            'update_time' => 'update_time',
            'sn' => 'sn',
            'log_type' => 'log_type',
            'event_type' => 'event_name',
            'event_name' => 'event_name',
            'action' => 'action',
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
            'application' => 'disposition',
            'disposition' => 'disposition',
            'source_interface' => 'source_interface',
            'destination_interface' => 'destination_interface',
            'pckt_len' => 'pckt_len',
            'ttl' => 'ttl',
            'sent_bytes' => 'sent_bytes',
            'rcvd_bytes' => 'rcvd_bytes',
        ];
    }
}
