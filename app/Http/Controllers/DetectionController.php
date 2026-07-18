<?php

namespace App\Http\Controllers;

use App\Models\DetectionResult;

use App\Services\IpGeolocationService;
use App\Support\AccessControl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetectionController extends Controller
{
    public function __construct(private IpGeolocationService $ipGeolocation)
    {
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $canViewDashboardDetectionCard = $user?->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD) ?? false;
        $canViewDashboardSuspiciousIpCard = $user?->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD) ?? false;
        $canViewDashboardDetection = $user?->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION) ?? false;

        // jumlah total traffic, normal, dan malware
        $totalTraffic = DetectionResult::count();
        $normalTotal = DetectionResult::where('prediction', 0)->count();
        $malwareTotal = DetectionResult::where('prediction', 1)->count();

        // persentase normal dan malware
        $normalPercentage = $totalTraffic > 0 ? ($normalTotal / $totalTraffic) * 100 : 0;
        $malwarePercentage = $totalTraffic > 0 ? ($malwareTotal / $totalTraffic) * 100 : 0;

        // ambil data deteksi terbaru
        $latestDetection = DetectionResult::query()
            ->latest('created_at')
            ->latest('id')
            ->first();

        // hitung jumlah IP mencurigakan
        $suspiciousIpCount = $canViewDashboardSuspiciousIpCard
            ? $this->publicSuspiciousIpCount()
            : 0;

        // ambil 8 deteksi terbaru
        $recentDetections = DetectionResult::query()
            ->latest('id')
            ->limit(8)
            ->get();

        // ambil 5 IP mencurigakan teratas
        $topSuspiciousIps = $canViewDashboardSuspiciousIpCard
            ? $this->topPublicSuspiciousIps()
            : collect();

        return view('dashboard', [
            'totalTraffic' => $totalTraffic,
            'normalTotal' => $normalTotal,
            'malwareTotal' => $malwareTotal,
            'normalPercentage' => $normalPercentage,
            'malwarePercentage' => $malwarePercentage,
            'latestDetection' => $latestDetection,
            'suspiciousIpCount' => $suspiciousIpCount,
            'recentDetections' => $recentDetections,
            'topSuspiciousIps' => $topSuspiciousIps,
            'canViewDashboardDetectionCard' => $canViewDashboardDetectionCard,
            'canViewDashboardSuspiciousIpCard' => $canViewDashboardSuspiciousIpCard,
            'canViewDashboardDetection' => $canViewDashboardDetection,
        ]);
    }

    // menampilkan aktivitas IP
    public function ipActivity(Request $request)
    {
        // ambil parameter IP dari query string
        $ipAddress = trim((string) $request->query('ip', ''));
        abort_if($ipAddress === '', 404);

        // ambil data aktivitas IP dari database
        $baseQuery = $this->ipActivityQuery($ipAddress);
        abort_unless((clone $baseQuery)->exists(), 404);

        // hitung total aktivitas, total alert, dan rata-rata confidence alert
        $totalActivities = (clone $baseQuery)->count();
        $totalAlerts = (clone $baseQuery)->where('prediction', 1)->count();
        $avgAlertConfidence = (clone $baseQuery)->where('prediction', 1)->avg('confidence');

        // ambil waktu aktivitas untuk membangun tren aktivitas
        $timeRecords = (clone $baseQuery)->get(['update_time', 'created_at']);
        $activityTimes = $timeRecords
            ->map(fn (DetectionResult $record) => $record->update_time ?: $record->created_at)
            ->filter()
            ->map(fn ($date) => $date->copy()->timezone('Asia/Jakarta'))
            ->sortBy(fn ($date) => $date->getTimestamp())
            ->values();

        // tentukan granularitas tren berdasarkan selisih waktu antara aktivitas pertama dan terakhir
        $firstSeen = $activityTimes->first();
        $lastSeen = $activityTimes->last();
        $trendGranularity = $firstSeen && $lastSeen && $firstSeen->diffInHours($lastSeen) > 48 ? 'day' : 'hour';
        $activityTrend = $this->buildActivityTrend($activityTimes, $trendGranularity);

        // ambil aktivitas terbaru dan alert terbaru untuk ditampilkan di halaman
        $activities = (clone $baseQuery)
            ->orderByDesc('update_time')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        // ambil 8 alert terbaru untuk ditampilkan di halaman
        $alerts = (clone $baseQuery)
            ->where('prediction', 1)
            ->orderByDesc('confidence')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // kembalikan view dengan data yang telah diambil dan dihitung
        return view('ip-activity', [
            'ipAddress' => $ipAddress,
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

    // menampilkan lokasi IP
    public function ipLocation(Request $request)
    {
        // ambil parameter IP dari query string
        $ipAddress = trim((string) $request->query('ip', ''));
        abort_if($ipAddress === '', 404);

        // ambil data aktivitas IP dari database
        $baseQuery = $this->ipActivityQuery($ipAddress);
        $totalActivities = (clone $baseQuery)->count();
        $totalAlerts = (clone $baseQuery)->where('prediction', 1)->count();

        abort_unless($totalActivities > 0, 404);

        // ambil record terbaru dan alert terbaru untuk ditampilkan di halaman
        $latestRecord = (clone $baseQuery)
            ->orderByDesc('update_time')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first(['update_time', 'created_at']);
        $latestAlert = (clone $baseQuery)
            ->where('prediction', 1)
            ->orderByDesc('confidence')
            ->orderByDesc('id')
            ->first(['event_name', 'confidence', 'update_time', 'created_at']);

        // ambil lokasi IP menggunakan layanan IpGeolocationService dengan fallback geo_src dari query
        return view('ip-location', [
            'pageTitle' => $totalAlerts > 0 ? 'Lokasi IP Mencurigakan' : 'Lokasi IP',
            'ipAddress' => $ipAddress,
            'ipLocation' => $this->ipGeolocation->lookup($ipAddress, $this->fallbackGeoCode($baseQuery)),
            'summary' => [
                'total_activities' => $totalActivities,
                'total_alerts' => $totalAlerts,
                'latest_seen' => $latestRecord?->update_time ?: $latestRecord?->created_at,
                'latest_alert' => $latestAlert,
            ],
        ]);
    }

    // membuat query untuk mengambil aktivitas berdasarkan IP
    private function ipActivityQuery(string $ipAddress): Builder
    {
        // buat query untuk mengambil data aktivitas dari tabel detection_results berdasarkan source_ip
        return DetectionResult::query()
            ->where('source_ip', $ipAddress);
    }

    // menghitung jumlah IP publik yang mencurigakan
    private function publicSuspiciousIpCount(): int
    {
        // ambil jumlah IP publik yang mencurigakan dari tabel detection_results dengan prediction = 1 dan source_ip tidak null
        return DetectionResult::query()
            ->where('prediction', 1)
            ->whereNotNull('source_ip')
            ->distinct()
            ->pluck('source_ip')
            ->filter(fn (?string $ipAddress) => $this->ipGeolocation->isPublicIp($ipAddress))
            ->count();
    }

    // mengambil 5 IP publik yang mencurigakan teratas berdasarkan jumlah deteksi
    private function topPublicSuspiciousIps(int $limit = 5)
    {
        // buat query untuk mengambil data IP publik yang mencurigakan dari tabel detection_results dengan prediction = 1 dan source_ip tidak null
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
            ->orderBy('source_ip')
            ->get()
            ->filter(fn (DetectionResult $ip) => $this->ipGeolocation->isPublicIp($ip->source_ip))
            ->take($limit)
            ->map(function (DetectionResult $ip) {
                $ip->setAttribute('location', $this->ipGeolocation->lookup($ip->source_ip, $ip->geo_src));

                return $ip;
            })
            ->values();
    }

    // mengambil nilai geo_src fallback dari query
    private function fallbackGeoCode(Builder $query): ?string
    {
        // ambil nilai geo_src fallback dari query dengan memfilter nilai null dan kosong
        return (clone $query)
            ->whereNotNull('geo_src')
            ->where('geo_src', '<>', '')
            ->value('geo_src');
    }

    // membuat lokasi fallback jika lookup gagal
    private function topColumnValues(Builder $query, string $column, int $limit = 5)
    {
        // buat query untuk mengambil nilai kolom tertentu dari tabel detection_results dengan menghitung jumlah kemunculan dan mengurutkan berdasarkan jumlah kemunculan
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

    // mengambil 5 destinasi teratas berdasarkan kombinasi destination_ip, destination_port, dan protocol
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

    // mengambil 5 nilai raw_record teratas berdasarkan kunci tertentu
    private function topRawRecordValues(Builder $query, array $keys, int $limit = 5)
    {
        $counts = [];

        // clone query untuk mengambil nilai raw_record dari tabel detection_results dan menghitung jumlah kemunculan berdasarkan kunci tertentu
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

        // ambil 5 nilai teratas dan kembalikan sebagai koleksi dengan label dan total
        return collect($counts)
            ->take($limit)
            ->map(fn ($total, $label) => [
                'label' => $label,
                'total' => (int) $total,
            ])
            ->values();
    }

    // mengambil nilai pertama dari raw_record berdasarkan kunci tertentu
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

    // membuat lokasi fallback jika lookup gagal
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
