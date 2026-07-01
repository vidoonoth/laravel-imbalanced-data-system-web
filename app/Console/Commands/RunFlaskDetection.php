<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use App\Services\FlaskDetectionService;
use Illuminate\Console\Command;
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
        $batchSize = (int) $this->option('batch-size');
        $dryRun = (bool) $this->option('dry-run');

        if (!$flaskService->isAvailable()) {
            $this->error('Flask API tidak tersedia. Cek apakah Flask app sudah running.');
            $this->line('Jalankan: php artisan detection:run-flask --check-health');
            return self::FAILURE;
        }

        $query = Dataset::query()
            ->whereDoesntHave('detectionResult')
            ->orderBy('id');

        if ($limit) {
            $query->limit($limit);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('Tidak ada dataset baru untuk dideteksi.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} dataset untuk dideteksi.");

        $processed = 0;
        $malware = 0;
        $normal = 0;

        $this->withProgressBar($total, function ($bar) use ($query, $batchSize, $flaskService, &$processed, &$malware, &$normal, $dryRun) {
            $query->chunk($batchSize, function ($datasets) use ($flaskService, &$processed, &$malware, &$normal, $bar, $dryRun) {
                $records = $datasets->map(fn ($dataset) => array_merge(
                    $dataset->payload,
                    ['_dataset_id' => $dataset->id]
                ))->toArray();

                try {
                    $results = $flaskService->detectBatch($records);

                    foreach ($results as $result) {
                        $datasetId = $result['_dataset_id'] ?? null;
                        
                        if (!$datasetId || $dryRun) {
                            continue;
                        }

                        DB::table('detection_results')->insert([
                            'dataset_id' => $datasetId,
                            'prediction' => $result['prediction'],
                            'prediction_label' => $result['prediction_label'],
                            'confidence' => $result['confidence'],
                            'probability_normal' => $result['probability_normal'],
                            'probability_attack' => $result['probability_attack'],
                            'source_ip' => $result['source_ip'] ?? null,
                            'destination_ip' => $result['destination_ip'] ?? null,
                            'source_port' => $result['source_port'] ?? null,
                            'destination_port' => $result['destination_port'] ?? null,
                            'protocol' => $result['protocol'] ?? null,
                            'detected_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        if ($result['prediction'] == 1) {
                            $malware++;
                        } else {
                            $normal++;
                        }
                    }

                    $processed += count($results);
                    $bar->advance(count($results));
                } catch (Throwable $e) {
                    $this->error("Error: {$e->getMessage()}");
                }
            });
        });

        $this->newLine(2);
        $this->info('=== Ringkasan Deteksi ===');
        $this->line("Total diproses: {$processed}");
        $this->line("Malware: {$malware}");
        $this->line("Normal: {$normal}");

        if ($dryRun) {
            $this->warn('Dry run - tidak ada data yang disimpan');
        }

        $this->newLine();
        $this->info('✓ Deteksi selesai.');

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
                ['Model Loaded', ($health['model_loaded'] ?? false) ? '✓ Yes' : '✗ No'],
            ]
        );

        if (isset($health['error'])) {
            $this->newLine();
            $this->error("Error: {$health['error']}");
        }

        $this->newLine();

        if (($health['status'] ?? '') === 'healthy') {
            $this->info('✓ Flask API siap digunakan.');
            return self::SUCCESS;
        } else {
            $this->error('✗ Flask API tidak siap.');
            return self::FAILURE;
        }
    }
}
