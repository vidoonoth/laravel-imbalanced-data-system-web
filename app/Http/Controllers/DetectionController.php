<?php

namespace App\Http\Controllers;

use App\Models\DetectionResult;
use App\Models\DetectionScan;
use App\Services\IpGeolocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetectionController extends Controller
{
    public function __construct(private IpGeolocationService $ipGeolocation)
    {
    }

    public function dashboard()
    {
        $scanQuery = DetectionScan::query()
            ->where('status', 'success');

        $totalScans = (clone $scanQuery)->count();
        $totalTraffic = (int) (clone $scanQuery)->sum('total_samples');
        $normalTotal = (int) (clone $scanQuery)->sum('normal_count');
        $malwareTotal = (int) (clone $scanQuery)->sum('attack_count');

        $normalPercentage = $totalTraffic > 0 ? ($normalTotal / $totalTraffic) * 100 : 0;
        $malwarePercentage = $totalTraffic > 0 ? ($malwareTotal / $totalTraffic) * 100 : 0;

        $latestScan = (clone $scanQuery)
            ->latest('completed_at')
            ->latest('id')
            ->first();

        $suspiciousIpCount = DetectionResult::query()
            ->where('prediction', 1)
            ->whereNotNull('source_ip')
            ->whereHas('scan', function ($query) {
                $query->where('status', 'success');
            })
            ->distinct()
            ->count('source_ip');

        $recentDetections = DetectionResult::query()
            ->with('scan')
            ->whereHas('scan', function ($query) {
                $query->where('status', 'success');
            })
            ->latest('id')
            ->limit(8)
            ->get();

        $topSuspiciousIps = DetectionResult::query()
            ->select(
                'source_ip',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(confidence) as avg_confidence'),
                DB::raw("MIN(NULLIF(geo_src, '')) as geo_src")
            )
            ->where('prediction', 1)
            ->whereNotNull('source_ip')
            ->whereHas('scan', function ($query) {
                $query->where('status', 'success');
            })
            ->groupBy('source_ip')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function (DetectionResult $ip) {
                $ip->setAttribute('location', $this->ipGeolocation->lookup($ip->source_ip, $ip->geo_src));

                return $ip;
            });

        $chartScans = (clone $scanQuery)
            ->latest('completed_at')
            ->latest('id')
            ->limit(8)
            ->get()
            ->reverse()
            ->values();

        $maxChartTotal = max((int) $chartScans->max('total_samples'), 1);

        return view('dashboard', [
            'totalScans' => $totalScans,
            'totalTraffic' => $totalTraffic,
            'normalTotal' => $normalTotal,
            'malwareTotal' => $malwareTotal,
            'normalPercentage' => $normalPercentage,
            'malwarePercentage' => $malwarePercentage,
            'latestScan' => $latestScan,
            'suspiciousIpCount' => $suspiciousIpCount,
            'recentDetections' => $recentDetections,
            'topSuspiciousIps' => $topSuspiciousIps,
            'chartScans' => $chartScans,
            'maxChartTotal' => $maxChartTotal,
        ]);
    }

    public function ipActivity(Request $request)
    {
        $ipAddress = trim((string) $request->query('ip', ''));
        abort_if($ipAddress === '', 404);

        $baseQuery = $this->ipActivityQuery($ipAddress);

        abort_unless((clone $baseQuery)->exists(), 404);

        $totalActivities = (clone $baseQuery)->count();
        $totalAlerts = (clone $baseQuery)->where('prediction', 1)->count();
        $avgAlertConfidence = (clone $baseQuery)->where('prediction', 1)->avg('confidence');
        $fallbackGeoCode = (clone $baseQuery)
            ->whereNotNull('geo_src')
            ->where('geo_src', '<>', '')
            ->value('geo_src');

        $timeRecords = (clone $baseQuery)->get(['update_time', 'created_at']);
        $activityTimes = $timeRecords
            ->map(fn (DetectionResult $record) => $record->update_time ?: $record->created_at)
            ->filter()
            ->map(fn ($date) => $date->copy()->timezone('Asia/Jakarta'))
            ->sortBy(fn ($date) => $date->getTimestamp())
            ->values();

        $firstSeen = $activityTimes->first();
        $lastSeen = $activityTimes->last();
        $trendGranularity = $firstSeen && $lastSeen && $firstSeen->diffInHours($lastSeen) > 48 ? 'day' : 'hour';
        $activityTrend = $this->buildActivityTrend($activityTimes, $trendGranularity);

        $activities = (clone $baseQuery)
            ->with('scan')
            ->orderByDesc('update_time')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $alerts = (clone $baseQuery)
            ->with('scan')
            ->where('prediction', 1)
            ->orderByDesc('confidence')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('ip-activity', [
            'ipAddress' => $ipAddress,
            'ipLocation' => $this->ipGeolocation->lookup($ipAddress, $fallbackGeoCode),
            'summary' => [
                'total_activities' => $totalActivities,
                'total_alerts' => $totalAlerts,
                'avg_alert_confidence' => $avgAlertConfidence,
                'first_seen' => $firstSeen,
                'last_seen' => $lastSeen,
            ],
            'topEvents' => $this->topColumnValues($baseQuery, 'event_name'),
            'topActions' => $this->topColumnValues($baseQuery, 'action'),
            'topLogTypes' => $this->topColumnValues($baseQuery, 'log_type'),
            'topDispositions' => $this->topColumnValues($baseQuery, 'disposition'),
            'topDestinations' => $this->topDestinations($baseQuery),
            'topEndpoints' => $this->topRawRecordValues($baseQuery, [
                'endpoint',
                'url',
                'path',
                'uri',
                'request_path',
                'request_uri',
                'request_url',
            ]),
            'topResponseStatuses' => $this->topRawRecordValues($baseQuery, [
                'status_code',
                'response_status',
                'http_status',
                'response_code',
            ]),
            'topSuspiciousEvents' => $this->topColumnValues((clone $baseQuery)->where('prediction', 1), 'event_name'),
            'topSuspiciousActions' => $this->topColumnValues((clone $baseQuery)->where('prediction', 1), 'action'),
            'activityTrend' => $activityTrend,
            'trendGranularity' => $trendGranularity,
            'maxTrendTotal' => max((int) $activityTrend->max('total'), 1),
            'activities' => $activities,
            'alerts' => $alerts,
        ]);
    }

    public function history(Request $request)
    {
        $scanQuery = DetectionScan::query()
            ->withCount('results')
            ->latest('created_at');

        if ($request->filled('q')) {
            $keyword = $request->string('q')->toString();

            $scanQuery->where(function ($query) use ($keyword) {
                $query->where('original_filename', 'like', "%{$keyword}%")
                    ->orWhere('status', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['processing', 'success', 'failed'], true)) {
            $scanQuery->where('status', $request->string('status')->toString());
        }

        $scans = $scanQuery->paginate(10)->withQueryString();

        $successQuery = DetectionScan::query()
            ->where('status', 'success');

        $summary = [
            'total_scans' => DetectionScan::query()->count(),
            'successful_scans' => (clone $successQuery)->count(),
            'total_samples' => (int) (clone $successQuery)->sum('total_samples'),
            'attack_count' => (int) (clone $successQuery)->sum('attack_count'),
        ];

        return view('detection-history', [
            'scans' => $scans,
            'summary' => $summary,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function show(DetectionScan $scan)
    {
        $results = $scan->results()
            ->orderBy('row_index')
            ->paginate(25);

        return view('detection-history-show', [
            'scan' => $scan,
            'results' => $results,
        ]);
    }

    private function ipActivityQuery(string $ipAddress): Builder
    {
        return DetectionResult::query()
            ->where('source_ip', $ipAddress)
            ->whereHas('scan', function ($query) {
                $query->where('status', 'success');
            });
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
            ]);
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
            });
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
                'label' => $label,
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
}
