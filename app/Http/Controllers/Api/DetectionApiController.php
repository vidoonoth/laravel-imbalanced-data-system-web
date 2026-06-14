<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetectionResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetectionApiController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'results' => 'required|array',
                'results.*.prediction' => 'required|integer',
            ]);

            $results = $request->input('results');
            $timestamp = now();

            DB::transaction(function () use ($results, $timestamp) {
                $rows = [];

                foreach ($results as $result) {
                    $rows[] = [
                        'row_index' => $this->nullableInt($result['row_index'] ?? $result['index'] ?? null) ?? 0,
                        'update_time' => $this->nullableDateTime($result['update_time'] ?? null),
                        'sn' => $this->limitedString($result['sn'] ?? null, 64),
                        'log_type' => $this->limitedString($result['log_type'] ?? null, 64),
                        'log' => $this->nullableText($result['log'] ?? $result['raw_log'] ?? null),
                        'event_name' => $this->limitedString($result['event_name'] ?? null, 128),
                        'disposition' => $this->limitedString($result['disposition'] ?? null, 32),
                        'priority' => $this->nullableInt($result['priority'] ?? null),
                        'protocol' => $this->limitedString($result['protocol'] ?? null, 64),
                        'source_ip' => $this->limitedString($result['source_ip'] ?? null, 64),
                        'destination_ip' => $this->limitedString($result['destination_ip'] ?? null, 64),
                        'source_port' => $this->nullableInt($result['source_port'] ?? null),
                        'destination_port' => $this->nullableInt($result['destination_port'] ?? null),
                        'source_interface' => $this->limitedString($result['source_interface'] ?? $result['source_intf'] ?? null, 64),
                        'destination_interface' => $this->limitedString($result['destination_interface'] ?? $result['destination_intf'] ?? null, 64),
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
                        'raw_record' => isset($result['raw_record']) ? (is_array($result['raw_record']) ? json_encode($result['raw_record']) : $result['raw_record']) : json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'detected_at' => $this->nullableDateTime($result['detected_at'] ?? null) ?? $timestamp,
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

            return response()->json([
                'status' => 'success',
                'message' => count($results) . ' results successfully saved.'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Detection Result Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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

    private function limitedString($value, int $limit): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, $limit);
    }
}
