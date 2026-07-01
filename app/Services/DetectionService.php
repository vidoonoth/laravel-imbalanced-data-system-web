<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class DetectionService
{
    public function runDetection(array $options = []): array
    {
        $pythonPath = $this->resolvePythonPath();
        $scriptPath = $this->resolveScriptPath();
        
        if (!file_exists($scriptPath)) {
            throw new RuntimeException("Detection script tidak ditemukan: {$scriptPath}");
        }

        $command = $this->buildCommand($pythonPath, $scriptPath, $options);
        
        Log::info('Running detection script', [
            'command' => implode(' ', $command),
            'options' => $options,
        ]);

        $process = new Process(
            $command,
            base_path(),
            $this->buildEnvironment(),
            null,
            $this->getTimeout($options)
        );

        $process->run();

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());

        if (!$process->isSuccessful()) {
            Log::error('Detection script failed', [
                'exit_code' => $process->getExitCode(),
                'output' => $output,
                'error' => $errorOutput,
            ]);

            throw new RuntimeException(
                "Detection gagal dengan exit code {$process->getExitCode()}: " . 
                ($errorOutput ?: $output)
            );
        }

        Log::info('Detection completed successfully', [
            'output' => $output,
        ]);

        return $this->parseOutput($output, $errorOutput);
    }

    public function checkPythonEnvironment(): array
    {
        $pythonPath = $this->resolvePythonPath();
        $scriptPath = $this->resolveScriptPath();
        $artifactsPath = $this->resolveArtifactsPath();

        $checks = [
            'python_exists' => file_exists($pythonPath) || $this->commandExists($pythonPath),
            'script_exists' => file_exists($scriptPath),
            'artifacts_exist' => is_dir($artifactsPath),
            'python_path' => $pythonPath,
            'script_path' => $scriptPath,
            'artifacts_path' => $artifactsPath,
        ];

        if ($checks['artifacts_exist']) {
            $requiredFiles = [
                'feature_columns.json',
                'preprocessor.joblib',
                'scarf_encoder.pt',
                'scarf_classifier.pt',
            ];

            $checks['artifacts_complete'] = true;
            $checks['missing_artifacts'] = [];

            foreach ($requiredFiles as $file) {
                $filePath = $artifactsPath . DIRECTORY_SEPARATOR . $file;
                if (!file_exists($filePath)) {
                    $checks['artifacts_complete'] = false;
                    $checks['missing_artifacts'][] = $file;
                }
            }
        } else {
            $checks['artifacts_complete'] = false;
            $checks['missing_artifacts'] = ['artifacts directory not found'];
        }

        if ($checks['python_exists']) {
            $checks['python_version'] = $this->getPythonVersion($pythonPath);
        }

        return $checks;
    }

    private function buildCommand(string $pythonPath, string $scriptPath, array $options): array
    {
        $command = [$pythonPath, $scriptPath];

        $source = $options['source'] ?? 'api';
        $command[] = '--source';
        $command[] = $source;

        if ($source === 'csv' && isset($options['input'])) {
            $command[] = '--input';
            $command[] = $options['input'];
        }

        if (isset($options['limit'])) {
            $command[] = '--limit';
            $command[] = (string) $options['limit'];
        }

        if (isset($options['batch_size'])) {
            $command[] = '--batch-size';
            $command[] = (string) $options['batch_size'];
        }

        if (isset($options['fetch_limit'])) {
            $command[] = '--fetch-limit';
            $command[] = (string) $options['fetch_limit'];
        }

        if (!empty($options['dry_run'])) {
            $command[] = '--dry-run';
        }

        if (!empty($options['force'])) {
            $command[] = '--force';
        }

        if (!empty($options['no_state'])) {
            $command[] = '--no-state';
        }

        return $command;
    }

    private function buildEnvironment(): array
    {
        $env = [];

        if ($apiKey = config('services.ml.api_key')) {
            $env['ML_API_KEY'] = $apiKey;
        }

        if ($apiUrl = config('services.ml.api_url')) {
            $env['ML_API_URL'] = $apiUrl;
        }

        if ($datasetsUrl = config('services.ml.datasets_url')) {
            $env['ML_DATASETS_URL'] = $datasetsUrl;
        }

        return array_merge($_ENV, $env);
    }

    private function resolvePythonPath(): string
    {
        $configured = config('services.ml.python_path');
        
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $candidates = [
            base_path('ml/venv/bin/python3'),
            base_path('ml/venv/bin/python'),
            base_path('ml/venv/Scripts/python.exe'),
            '/usr/bin/python3',
            '/usr/local/bin/python3',
            'python3',
            'python',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) || $this->commandExists($candidate)) {
                return $candidate;
            }
        }

        return 'python3';
    }

    private function resolveScriptPath(): string
    {
        $configured = config('services.ml.script_path');
        
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return base_path('ml/detect_and_send.py');
    }

    private function resolveArtifactsPath(): string
    {
        $configured = config('services.ml.artifacts_path');
        
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return base_path('ml/model_artifacts');
    }

    private function getTimeout(array $options): int
    {
        if (isset($options['timeout'])) {
            return (int) $options['timeout'];
        }

        return (int) config('services.ml.timeout', 600);
    }

    private function parseOutput(string $output, string $errorOutput): array
    {
        $lines = array_filter(
            explode("\n", $output . "\n" . $errorOutput),
            fn ($line) => trim($line) !== ''
        );

        $result = [
            'success' => true,
            'output' => $output,
            'error_output' => $errorOutput,
            'lines' => $lines,
            'summary' => [],
        ];

        foreach ($lines as $line) {
            if (preg_match('/(\d+)\s+record\s+diprediksi/', $line, $matches)) {
                $result['summary']['predicted'] = (int) $matches[1];
            }
            if (preg_match('/(\d+)\s+record\s+dikirim/', $line, $matches)) {
                $result['summary']['sent'] = (int) $matches[1];
            }
            if (preg_match('/(\d+)\s+malware/', $line, $matches)) {
                $result['summary']['malware'] = (int) $matches[1];
            }
            if (preg_match('/(\d+)\s+normal/', $line, $matches)) {
                $result['summary']['normal'] = (int) $matches[1];
            }
        }

        return $result;
    }

    private function commandExists(string $command): bool
    {
        $testCommand = PHP_OS_FAMILY === 'Windows'
            ? ['where', $command]
            : ['which', $command];

        $process = new Process($testCommand);
        $process->run();

        return $process->isSuccessful();
    }

    private function getPythonVersion(string $pythonPath): ?string
    {
        try {
            $process = new Process([$pythonPath, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                return trim($process->getOutput() ?: $process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
