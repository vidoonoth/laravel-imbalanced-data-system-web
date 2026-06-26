<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetectionResult;
use App\Services\IpGeolocationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetectionApiController extends Controller
{
    public function __construct(private IpGeolocationService $ipGeolocation)
    {
    }

    public function dashboard(Request $request)
    {
        $recentLimit = $this->limitFromRequest($request, 'recent_limit', 8, 50);
        $suspiciousLimit = $this->limitFromRequest($request, 'suspicious_limit', 5, 50);

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => $this->dashboardSummary(),
                'recent_detections' => $this->recentDetections($recentLimit),
                'suspicious_ips' => $this->topSuspiciousIps($suspiciousLimit),
            ],
        ]);
    }

    public function suspiciousIps(Request $request)
    {
        $limit = $this->limitFromRequest($request, 'limit', 5, 100);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $this->topSuspiciousIps($limit),
            ],
        ]);
    }

    public function suspiciousIpDetail(Request $request)
    {
        $ipAddress = $this->ipAddressFromRequest($request);

        if ($ipAddress === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter ip wajib diisi.',
            ], 422);
        }

        $baseQuery = $this->ipActivityQuery($ipAddress);

        if (! (clone $baseQuery)->exists()) {
            return $this->ipNotFoundResponse($ipAddress);
        }

        $perPage = $this->limitFromRequest($request, 'per_page', 25, 100);
        $totalActivities = (clone $baseQuery)->count();
        $totalAlerts = (clone $baseQuery)->where('prediction', 1)->count();
        $avgAlertConfidence = (clone $baseQuery)->where('prediction', 1)->avg('confidence');

        $activityTimes = (clone $baseQuery)
            ->get(['update_time', 'created_at'])
            ->map(fn (DetectionResult $record) => $record->update_time ?: $record->created_at)
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->timezone('Asia/Jakarta'))
            ->sortBy(fn ($date) => $date->getTimestamp())
            ->values();

        $firstSeen = $activityTimes->first();
        $lastSeen = $activityTimes->last();
        $trendGranularity = $firstSeen && $lastSeen && $firstSeen->diffInHours($lastSeen) > 48 ? 'day' : 'hour';
        $activityTrend = $this->buildActivityTrend($activityTimes, $trendGranularity);

        $activities = (clone $baseQuery)
            ->orderByDesc('update_time')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $alerts = (clone $baseQuery)
            ->where('prediction', 1)
            ->orderByDesc('confidence')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (DetectionResult $record) => $this->detectionRecordPayload($record))
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'ip' => $ipAddress,
                'summary' => [
                    'total_activities' => $totalActivities,
                    'total_alerts' => $totalAlerts,
                    'avg_alert_confidence' => $this->nullableFloat($avgAlertConfidence),
                    'first_seen' => $this->formatDateTime($firstSeen),
                    'last_seen' => $this->formatDateTime($lastSeen),
                ],
                'top_events' => $this->topColumnValues($baseQuery, 'event_name'),
                'top_actions' => $this->topColumnValues($baseQuery, 'action'),
                'top_log_types' => $this->topColumnValues($baseQuery, 'log_type'),
                'top_dispositions' => $this->topColumnValues($baseQuery, 'disposition'),
                'top_destinations' => $this->topDestinations($baseQuery),
                'top_endpoints' => $this->topRawRecordValues($baseQuery, [
                    'endpoint',
                    'url',
                    'path',
                    'uri',
                    'request_path',
                    'request_uri',
                    'request_url',
                ]),
                'top_response_statuses' => $this->topRawRecordValues($baseQuery, [
                    'status_code',
                    'response_status',
                    'http_status',
                    'response_code',
                ]),
                'top_suspicious_events' => $this->topColumnValues((clone $baseQuery)->where('prediction', 1), 'event_name'),
                'top_suspicious_actions' => $this->topColumnValues((clone $baseQuery)->where('prediction', 1), 'action'),
                'activity_trend' => $activityTrend,
                'trend_granularity' => $trendGranularity,
                'alerts' => $alerts,
                'activities' => $this->paginatedDetectionPayload($activities),
            ],
        ]);
    }

    public function suspiciousIpLocation(Request $request)
    {
        $ipAddress = $this->ipAddressFromRequest($request);

        if ($ipAddress === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter ip wajib diisi.',
            ], 422);
        }

        $baseQuery = $this->ipActivityQuery($ipAddress);
        $totalActivities = (clone $baseQuery)->count();
        $totalAlerts = (clone $baseQuery)->where('prediction', 1)->count();

        if ($totalActivities === 0) {
            return $this->ipNotFoundResponse($ipAddress);
        }

        $latestRecord = (clone $baseQuery)
            ->orderByDesc('update_time')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first(['update_time', 'created_at']);

        $latestAlert = (clone $baseQuery)
            ->where('prediction', 1)
            ->orderByDesc('confidence')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'ip' => $ipAddress,
                'location' => $this->ipGeolocation->lookup($ipAddress, $this->fallbackGeoCode($baseQuery)),
                'summary' => [
                    'total_activities' => $totalActivities,
                    'total_alerts' => $totalAlerts,
                    'latest_seen' => $this->formatDateTime($latestRecord?->update_time ?: $latestRecord?->created_at),
                    'latest_alert' => $latestAlert ? $this->detectionRecordPayload($latestAlert) : null,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'results' => 'required|array',
                'results.*.prediction' => 'required|integer',
                'results.*.dataset_id' => 'nullable|integer|exists:datasets,id',
            ]);

            $results = $request->input('results');
            $timestamp = now();

            DB::transaction(function () use ($results, $timestamp) {
                $rows = [];

                foreach ($results as $result) {
                    $datasetId = $this->nullableInt($result['dataset_id'] ?? null);
                    $row = $this->detectionRowFromResult($result, $timestamp);

                    if ($datasetId !== null) {
                        DetectionResult::query()->updateOrCreate(
                            ['dataset_id' => $datasetId],
                            $row
                        );

                        continue;
                    }

                    $row['raw_record'] = is_array($row['raw_record'])
                        ? json_encode($row['raw_record'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        : $row['raw_record'];
                    $row['created_at'] = $timestamp;
                    $row['updated_at'] = $timestamp;
                    $rows[] = $row;

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

    private function detectionRowFromResult(array $result, $timestamp): array
    {
        return [
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
            'raw_record' => $result['raw_record'] ?? $result,
            'detected_at' => $this->nullableDateTime($result['detected_at'] ?? null) ?? $timestamp,
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

    private function limitedString($value, int $limit): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, $limit);
    }

    private function dashboardSummary(): array
    {
        $totalTraffic = DetectionResult::count();
        $normalTotal = DetectionResult::where('prediction', 0)->count();
        $malwareTotal = DetectionResult::where('prediction', 1)->count();
        $latestDetection = DetectionResult::query()
            ->latest('detected_at')
            ->latest('id')
            ->first();

        return [
            'total_traffic' => $totalTraffic,
            'normal_total' => $normalTotal,
            'malware_total' => $malwareTotal,
            'suspicious_ip_count' => DetectionResult::query()
                ->where('prediction', 1)
                ->whereNotNull('source_ip')
                ->distinct()
                ->count('source_ip'),
            'normal_percentage' => $totalTraffic > 0 ? round(($normalTotal / $totalTraffic) * 100, 2) : 0.0,
            'malware_percentage' => $totalTraffic > 0 ? round(($malwareTotal / $totalTraffic) * 100, 2) : 0.0,
            'latest_detection' => $latestDetection ? $this->detectionRecordPayload($latestDetection) : null,
        ];
    }

    private function recentDetections(int $limit)
    {
        return DetectionResult::query()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (DetectionResult $record) => $this->detectionRecordPayload($record))
            ->values();
    }

    private function topSuspiciousIps(int $limit)
    {
        return DetectionResult::query()
            ->select(
                'source_ip',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(confidence) as avg_confidence'),
                DB::raw("MIN(NULLIF(geo_src, '')) as geo_src")
            )
            ->where('prediction', 1)
            ->whereNotNull('source_ip')
            ->groupBy('source_ip')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function (DetectionResult $ip) {
                return [
                    'source_ip' => $ip->source_ip,
                    'total' => (int) $ip->total,
                    'avg_confidence' => $this->nullableFloat($ip->avg_confidence),
                    'avg_confidence_percentage' => $ip->avg_confidence !== null
                        ? round(((float) $ip->avg_confidence) * 100, 2)
                        : null,
                    'geo_src' => $ip->geo_src,
                    'location' => $this->ipGeolocation->lookup($ip->source_ip, $ip->geo_src),
                ];
            })
            ->values();
    }

    private function ipActivityQuery(string $ipAddress): Builder
    {
        return DetectionResult::query()
            ->where('source_ip', $ipAddress);
    }

    private function fallbackGeoCode(Builder $query): ?string
    {
        return (clone $query)
            ->whereNotNull('geo_src')
            ->where('geo_src', '<>', '')
            ->value('geo_src');
    }

    private function topColumnValues(Builder $query, string $column, int $limit = 5)
    {
        return (clone $query)
            ->select($column, DB::raw('COUNT(*) as total'))
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->orderBy($column)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->{$column},
                'total' => (int) $row->total,
            ])
            ->values();
    }

    private function topDestinations(Builder $query, int $limit = 5)
    {
        return (clone $query)
            ->select('destination_ip', 'destination_port', 'protocol', DB::raw('COUNT(*) as total'))
            ->where(function ($query) {
                $query->whereNotNull('destination_ip')
                    ->orWhereNotNull('destination_port')
                    ->orWhereNotNull('protocol');
            })
            ->groupBy('destination_ip', 'destination_port', 'protocol')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function (DetectionResult $row) {
                $address = $row->destination_ip ?: '-';
                $port = $row->destination_port !== null ? ':' . $row->destination_port : '';
                $protocol = $row->protocol ? ' / ' . $row->protocol : '';

                return [
                    'label' => $address . $port . $protocol,
                    'total' => (int) $row->total,
                ];
            })
            ->values();
    }

    private function topRawRecordValues(Builder $query, array $keys, int $limit = 5)
    {
        $counts = [];

        (clone $query)
            ->whereNotNull('raw_record')
            ->get(['raw_record'])
            ->each(function (DetectionResult $record) use (&$counts, $keys) {
                $value = $this->firstRawRecordValue($record->raw_record, $keys);

                if ($value === null) {
                    return;
                }

                $counts[$value] = ($counts[$value] ?? 0) + 1;
            });

        arsort($counts);

        return collect($counts)
            ->take($limit)
            ->map(fn ($total, $label) => [
                'label' => (string) $label,
                'total' => (int) $total,
            ])
            ->values();
    }

    private function firstRawRecordValue($rawRecord, array $keys): ?string
    {
        if (! is_array($rawRecord)) {
            return null;
        }

        $normalizedRecord = array_change_key_case($rawRecord, CASE_LOWER);

        foreach ($keys as $key) {
            $value = $normalizedRecord[strtolower($key)] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function buildActivityTrend($activityTimes, string $granularity)
    {
        $buckets = [];

        foreach ($activityTimes as $date) {
            $key = $granularity === 'day' ? $date->format('Y-m-d') : $date->format('Y-m-d H');
            $label = $granularity === 'day' ? $date->format('d/m/Y') : $date->format('d/m H:00');

            if (! isset($buckets[$key])) {
                $buckets[$key] = [
                    'label' => $label,
                    'total' => 0,
                ];
            }

            $buckets[$key]['total']++;
        }

        ksort($buckets);

        return collect(array_values($buckets));
    }

    private function paginatedDetectionPayload($paginator): array
    {
        return [
            'data' => $paginator->getCollection()
                ->map(fn (DetectionResult $record) => $this->detectionRecordPayload($record))
                ->values(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    private function detectionRecordPayload(DetectionResult $record): array
    {
        return [
            'id' => $record->id,
            'row_index' => $record->row_index,
            'detected_at' => $this->formatDateTime($record->detected_at),
            'update_time' => $this->formatDateTime($record->update_time),
            'sn' => $record->sn,
            'log_type' => $record->log_type,
            'log' => $record->log,
            'event_name' => $record->event_name,
            'disposition' => $record->disposition,
            'priority' => $record->priority,
            'protocol' => $record->protocol,
            'source_ip' => $record->source_ip,
            'destination_ip' => $record->destination_ip,
            'source_port' => $record->source_port,
            'destination_port' => $record->destination_port,
            'source_interface' => $record->source_interface,
            'destination_interface' => $record->destination_interface,
            'policy' => $record->policy,
            'pckt_len' => $record->pckt_len,
            'ttl' => $record->ttl,
            'sent_bytes' => $record->sent_bytes,
            'rcvd_bytes' => $record->rcvd_bytes,
            'geo_src' => $record->geo_src,
            'geo_dst' => $record->geo_dst,
            'action' => $record->action,
            'prediction' => $record->prediction,
            'prediction_label' => $record->prediction_label,
            'confidence' => $this->nullableFloat($record->confidence),
            'probability_normal' => $this->nullableFloat($record->probability_normal),
            'probability_attack' => $this->nullableFloat($record->probability_attack),
            'raw_record' => $record->raw_record,
            'created_at' => $this->formatDateTime($record->created_at),
            'updated_at' => $this->formatDateTime($record->updated_at),
        ];
    }

    private function formatDateTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->timezone('Asia/Jakarta')
                ->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }

    private function ipAddressFromRequest(Request $request): ?string
    {
        $ipAddress = trim((string) $request->query('ip', ''));

        return $ipAddress !== '' ? $ipAddress : null;
    }

    private function ipNotFoundResponse(string $ipAddress)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'IP address was not found in detection results.',
            'ip' => $ipAddress,
        ], 404);
    }

    private function limitFromRequest(Request $request, string $key, int $default, int $max): int
    {
        $value = $request->query($key, $default);

        if (! is_numeric($value)) {
            return $default;
        }

        return min(max((int) $value, 1), $max);
    }
}
