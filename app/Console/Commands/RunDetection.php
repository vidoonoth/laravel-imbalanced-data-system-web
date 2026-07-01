<?php

namespace App\Console\Commands;

use App\Services\DetectionService;
use Illuminate\Console\Command;
use Throwable;

class RunDetection extends Command
{
    protected $signature = 'detection:run
        {--source=api : Sumber data: api (dari Laravel DB) atau csv (dari file lokal)}
        {--input= : Path file CSV atau folder (untuk --source=csv)}
        {--limit= : Batasi jumlah record yang diproses}
        {--batch-size=500 : Jumlah record per batch ke API}
        {--fetch-limit=500 : Jumlah dataset yang diambil per request}
        {--timeout= : Timeout dalam detik}
        {--dry-run : Prediksi tanpa mengirim hasil ke API}
        {--force : Proses ulang dari awal}
        {--no-state : Jangan simpan state pemrosesan}
        {--check-env : Cek environment Python dan artifact sebelum run}';

    protected $description = 'Menjalankan deteksi malware menggunakan model SCARF dan mengirim hasil ke database.';

    public function handle(DetectionService $detectionService): int
    {
        if ($this->option('check-env')) {
            return $this->checkEnvironment($detectionService);
        }

        $this->info('Memulai deteksi malware...');

        $options = [
            'source' => $this->option('source'),
            'batch_size' => (int) $this->option('batch-size'),
            'fetch_limit' => (int) $this->option('fetch-limit'),
            'dry_run' => (bool) $this->option('dry-run'),
            'force' => (bool) $this->option('force'),
            'no_state' => (bool) $this->option('no-state'),
        ];

        if ($this->option('input')) {
            $options['input'] = $this->option('input');
        }

        if ($this->option('limit')) {
            $options['limit'] = (int) $this->option('limit');
        }

        if ($this->option('timeout')) {
            $options['timeout'] = (int) $this->option('timeout');
        }

        try {
            $result = $detectionService->runDetection($options);

            if (!empty($result['lines'])) {
                foreach ($result['lines'] as $line) {
                    $this->line($line);
                }
            }

            if (!empty($result['summary'])) {
                $this->newLine();
                $this->info('=== Ringkasan Deteksi ===');
                
                if (isset($result['summary']['predicted'])) {
                    $this->line("Total diprediksi: {$result['summary']['predicted']} record");
                }
                
                if (isset($result['summary']['sent'])) {
                    $this->line("Total dikirim: {$result['summary']['sent']} record");
                }
                
                if (isset($result['summary']['malware'])) {
                    $this->line("Malware: {$result['summary']['malware']} record");
                }
                
                if (isset($result['summary']['normal'])) {
                    $this->line("Normal: {$result['summary']['normal']} record");
                }
            }

            $this->newLine();
            $this->info('✓ Deteksi selesai dengan sukses.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('✗ Deteksi gagal: ' . $exception->getMessage());
            
            if ($this->output->isVerbose()) {
                $this->error($exception->getTraceAsString());
            } else {
                $this->line('Jalankan dengan -v untuk detail error.');
            }

            return self::FAILURE;
        }
    }

    private function checkEnvironment(DetectionService $detectionService): int
    {
        $this->info('Memeriksa environment Python dan ML artifacts...');
        $this->newLine();

        $checks = $detectionService->checkPythonEnvironment();

        $this->table(
            ['Check', 'Status'],
            [
                ['Python executable', $checks['python_exists'] ? '✓ Found' : '✗ Not found'],
                ['Detection script', $checks['script_exists'] ? '✓ Found' : '✗ Not found'],
                ['Artifacts directory', $checks['artifacts_exist'] ? '✓ Found' : '✗ Not found'],
                ['Artifacts complete', $checks['artifacts_complete'] ? '✓ Complete' : '✗ Incomplete'],
            ]
        );

        $this->newLine();
        $this->line("Python path: {$checks['python_path']}");
        
        if (isset($checks['python_version'])) {
            $this->line("Python version: {$checks['python_version']}");
        }
        
        $this->line("Script path: {$checks['script_path']}");
        $this->line("Artifacts path: {$checks['artifacts_path']}");

        if (!empty($checks['missing_artifacts'])) {
            $this->newLine();
            $this->warn('Missing artifacts:');
            foreach ($checks['missing_artifacts'] as $artifact) {
                $this->line("  - {$artifact}");
            }
        }

        $allOk = $checks['python_exists'] 
            && $checks['script_exists'] 
            && $checks['artifacts_exist'] 
            && $checks['artifacts_complete'];

        $this->newLine();
        
        if ($allOk) {
            $this->info('✓ Environment siap untuk menjalankan deteksi.');
            return self::SUCCESS;
        } else {
            $this->error('✗ Environment belum siap. Perbaiki masalah di atas sebelum menjalankan deteksi.');
            return self::FAILURE;
        }
    }
}
