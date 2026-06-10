<?php

namespace App\Http\Controllers;

use App\Models\DetectionResult;
use App\Models\DetectionScan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MLController extends Controller
{
    /**
     * ML Backend URL
     */
    private $mlBackendUrl = 'http://127.0.0.1:5012/api';

    /**
     * Load and preprocess dataset
     */
    public function loadData()
    {
        try {
            $response = Http::timeout(300)->post($this->mlBackendUrl . '/data/load');

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to load data'
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Data Load Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze dataset imbalance
     */
    public function analyzeImbalance()
    {
        try {
            $response = Http::get($this->mlBackendUrl . '/imbalance/analyze');

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to analyze imbalance'
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Imbalance Analysis Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle class imbalance with SMOTE or other methods
     */
    public function handleImbalance(Request $request)
    {
        try {
            $validated = $request->validate([
                'method' => 'in:smote,combined',
                'sampling_ratio' => 'numeric|between:0,1'
            ]);

            $response = Http::post($this->mlBackendUrl . '/imbalance/handle', $validated);

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to handle imbalance'
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Imbalance Handling Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Train contrastive learning + classification model
     */
    public function trainModel(Request $request)
    {
        try {
            $validated = $request->validate([
                'epochs' => 'integer|min:10|max:1000',
                'batch_size' => 'integer|min:8|max:256',
                'learning_rate' => 'numeric|min:0.00001|max:0.1'
            ]);

            // Set high timeout for training
            $response = Http::timeout(600)->post($this->mlBackendUrl . '/model/train', $validated);

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to train model',
                    'status_code' => $response->getStatusCode()
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Model Training Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Evaluate model on test set
     */
    public function evaluateModel()
    {
        try {
            $response = Http::timeout(300)->get($this->mlBackendUrl . '/model/evaluate');

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to evaluate model'
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Model Evaluation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make malware detection inference from file upload
     */
    public function predictFromFile(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:51200' // 50MB max
            ]);

            [$payload, $statusCode] = $this->predictUploadedFile($validated['file']);

            return response()->json($payload, $statusCode);
        } catch (\Exception $e) {
            Log::error('ML Prediction Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save the uploaded file, send it to Flask, then persist every detection row.
     */
    private function predictUploadedFile(UploadedFile $file): array
    {
        $fileContents = $file->get();
        $storedPath = $this->storeUploadedDetectionFile($file, $fileContents);

        $scan = DetectionScan::create([
            'user_id' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'file_size' => $file->getSize() ?? strlen($fileContents),
            'mime_type' => $file->getClientMimeType(),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $response = Http::timeout(600)
                ->attach('file', $fileContents, $file->getClientOriginalName())
                ->post($this->mlBackendUrl . '/predict');

            if ($response->failed()) {
                Log::error('Flask prediction failed: ' . $response->body());

                $scan->update([
                    'status' => 'failed',
                    'error_message' => 'Prediction failed',
                    'completed_at' => now(),
                ]);

                return [[
                    'status' => 'error',
                    'message' => 'Prediction failed',
                    'details' => $response->json() ?: ['raw' => $response->body()],
                    'scan' => $this->scanResponse($scan->fresh()),
                ], $response->getStatusCode()];
            }

            $payload = $response->json() ?: [];

            if (($payload['status'] ?? null) !== 'success') {
                $scan->update([
                    'status' => 'failed',
                    'error_message' => $this->limitedErrorMessage(
                        $payload['message'] ?? $payload['error'] ?? 'Prediction response is not successful'
                    ),
                    'completed_at' => now(),
                ]);

                $payload['scan'] = $this->scanResponse($scan->fresh());

                return [$payload, 422];
            }

            $this->persistDetectionResults($scan, $payload);

            $payload['scan'] = $this->scanResponse($scan->fresh());

            return [$payload, 200];
        } catch (\Throwable $e) {
            $scan->update([
                'status' => 'failed',
                'error_message' => $this->limitedErrorMessage($e->getMessage()),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    private function storeUploadedDetectionFile(UploadedFile $file, string $fileContents): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'csv';
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'network-log';
        $storedPath = sprintf(
            'detection_uploads/%s_%s_%s.%s',
            now()->format('Ymd_His'),
            Str::random(8),
            $baseName,
            $extension
        );

        Storage::disk('local')->put($storedPath, $fileContents);

        return $storedPath;
    }

    private function persistDetectionResults(DetectionScan $scan, array $payload): void
    {
        $summary = $payload['summary'] ?? [];
        $results = $payload['results'] ?? [];

        DB::transaction(function () use ($scan, $summary, $results) {
            $scan->results()->delete();
            $scan->update([
                'status' => 'success',
                'total_samples' => $this->nullableInt($summary['total_samples'] ?? null) ?? count($results),
                'normal_count' => $this->nullableInt($summary['normal_count'] ?? null) ?? 0,
                'attack_count' => $this->nullableInt($summary['attack_count'] ?? null) ?? 0,
                'normal_percentage' => $this->nullableFloat($summary['normal_percentage'] ?? null) ?? 0,
                'attack_percentage' => $this->nullableFloat($summary['attack_percentage'] ?? null) ?? 0,
                'raw_summary' => $summary,
                'error_message' => null,
                'completed_at' => now(),
            ]);

            $rows = [];
            $timestamp = now();

            foreach ($results as $result) {
                $rows[] = [
                    'detection_scan_id' => $scan->id,
                    'row_index' => $this->nullableInt($result['index'] ?? null) ?? 0,
                    'update_time' => $this->nullableDateTime($result['update_time'] ?? null),
                    'sn' => $this->limitedString($result['sn'] ?? null, 64),
                    'log_type' => $this->limitedString($result['log_type'] ?? null, 64),
                    'log' => $this->nullableText($result['log'] ?? null),
                    'event_name' => $this->limitedString($result['event_name'] ?? null, 128),
                    'disposition' => $this->limitedString($result['disposition'] ?? null, 32),
                    'priority' => $this->nullableInt($result['priority'] ?? null),
                    'protocol' => $this->limitedString($result['protocol'] ?? null, 64),
                    'source_ip' => $this->limitedString($result['source_ip'] ?? null, 64),
                    'destination_ip' => $this->limitedString($result['destination_ip'] ?? null, 64),
                    'source_port' => $this->nullableInt($result['source_port'] ?? null),
                    'destination_port' => $this->nullableInt($result['destination_port'] ?? null),
                    'source_interface' => $this->limitedString($result['source_interface'] ?? null, 64),
                    'destination_interface' => $this->limitedString($result['destination_interface'] ?? null, 64),
                    'policy' => $this->limitedString($result['policy'] ?? null, 255),
                    'pckt_len' => $this->nullableInt($result['pckt_len'] ?? null),
                    'ttl' => $this->nullableInt($result['ttl'] ?? null),
                    'sent_bytes' => $this->nullableInt($result['sent_bytes'] ?? null),
                    'rcvd_bytes' => $this->nullableInt($result['rcvd_bytes'] ?? null),
                    'geo_src' => $this->limitedString($result['geo_src'] ?? null, 16),
                    'geo_dst' => $this->limitedString($result['geo_dst'] ?? null, 16),
                    'action' => $this->limitedString($result['action'] ?? null, 255),
                    'prediction' => $this->nullableInt($result['prediction'] ?? null),
                    'prediction_label' => $this->limitedString($result['prediction_label'] ?? null, 32),
                    'confidence' => $this->nullableFloat($result['confidence'] ?? null),
                    'probability_normal' => $this->nullableFloat($result['probability_normal'] ?? null),
                    'probability_attack' => $this->nullableFloat($result['probability_attack'] ?? null),
                    'raw_record' => json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                if (count($rows) >= 500) {
                    DetectionResult::insert($rows);
                    $rows = [];
                }
            }

            if ($rows !== []) {
                DetectionResult::insert($rows);
            }
        });
    }

    private function scanResponse(DetectionScan $scan): array
    {
        return [
            'id' => $scan->id,
            'filename' => $scan->original_filename,
            'status' => $scan->status,
            'history_url' => route('detection.history.show', $scan),
            'created_at' => optional($scan->created_at)->toDateTimeString(),
        ];
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = is_string($value) ? str_replace(',', '.', trim($value)) : $value;

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function nullableDateTime($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullableText($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function limitedErrorMessage($value): string
    {
        return $this->limitedString($value ?: 'Prediction failed', 240) ?? 'Prediction failed';
    }

    private function limitedString($value, int $limit): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, $limit);
    }

    /**
     * Make malware detection inference from batch data
     */
    public function predictBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'records' => 'required|array|min:1'
            ]);

            $response = Http::timeout(600)->post($this->mlBackendUrl . '/batch-predict', $validated);

            if ($response->failed()) {
                Log::error('Flask batch prediction failed: ' . $response->body());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batch prediction failed',
                    'details' => $response->json()
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Batch Prediction Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make malware detection inference (legacy)
     */
    public function detect(Request $request)
    {
        try {
            $validated = $request->validate([
                'network_log' => 'required|array'
            ]);

            $response = Http::post($this->mlBackendUrl . '/detect', $validated);

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Detection failed'
                ], $response->getStatusCode());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('ML Detection Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ML backend health status
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)->get($this->mlBackendUrl . '/health');

            if ($response->ok()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => 'error',
                'message' => 'ML backend not responding'
            ], 503);
        } catch (\Exception $e) {
            Log::error('ML Health Check Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'ML backend unavailable'
            ], 503);
        }
    }
}
