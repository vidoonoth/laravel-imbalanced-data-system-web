<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FlaskDetectionService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ml.flask_url', 'http://localhost:5000'), '/');
        $this->apiKey = config('services.ml.api_key', 'ml_api_key_secret_2026');
        $this->timeout = (int) config('services.ml.timeout', 60);
    }

    public function detectBatch(array $records): array
    {
        if (empty($records)) {
            throw new RuntimeException('No records to detect');
        }

        Log::info('Calling Flask API for batch detection', [
            'count' => count($records),
            'url' => $this->baseUrl,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/api/detect/batch", [
                    'records' => $records,
                ]);

            if (!$response->successful()) {
                throw new RuntimeException(
                    "Flask API error ({$response->status()}): " . $response->body()
                );
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                throw new RuntimeException(
                    "Detection failed: " . ($data['error'] ?? 'Unknown error')
                );
            }

            Log::info('Batch detection completed', [
                'count' => $data['count'] ?? 0,
            ]);

            return $data['results'] ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Flask API connection failed', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl,
            ]);

            throw new RuntimeException(
                'Cannot connect to ML Detection API. Ensure Flask app is running.'
            );
        }
    }

    public function detectSingle(array $record): array
    {
        Log::info('Calling Flask API for single detection');

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/api/detect/single", $record);

            if (!$response->successful()) {
                throw new RuntimeException(
                    "Flask API error ({$response->status()}): " . $response->body()
                );
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                throw new RuntimeException(
                    "Detection failed: " . ($data['error'] ?? 'Unknown error')
                );
            }

            return $data['result'] ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Flask API connection failed', [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Cannot connect to ML Detection API'
            );
        }
    }

    public function checkHealth(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/health");

            if (!$response->successful()) {
                return [
                    'status' => 'unhealthy',
                    'model_loaded' => false,
                    'error' => "HTTP {$response->status()}",
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            return [
                'status' => 'unreachable',
                'model_loaded' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function isAvailable(): bool
    {
        $health = $this->checkHealth();
        return ($health['status'] ?? 'unhealthy') === 'healthy';
    }
}
