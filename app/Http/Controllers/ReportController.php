<?php

namespace App\Http\Controllers;

use App\Models\DetectionResult;

use App\Services\IpGeolocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct(private IpGeolocationService $ipGeolocation)
    {
    }

    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        
        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];
        
        $detectionQuery = DetectionResult::query()
            ->latest('detected_at')
            ->latest('id');
             
        if ($dateFrom) {
            $detectionQuery->where('detected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $detectionQuery->where('detected_at', '<=', $dateTo);
        }
        
        $data['recentDetections'] = $detectionQuery->paginate(10)->withQueryString();

        return view('report.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        
        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];
        
        $detectionQuery = DetectionResult::query()
            ->latest('detected_at')
            ->latest('id');
             
        if ($dateFrom) {
            $detectionQuery->where('detected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $detectionQuery->where('detected_at', '<=', $dateTo);
        }
        
        $data['recentDetections'] = $detectionQuery->limit(200)->get();
        $data['isPdf'] = true;

        $pdf = Pdf::loadView('report.pdf', $data)->setPaper('a4', 'portrait');
        
        $filename = 'laporan-deteksi-malware-' . now()->format('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    private function getReportData(Request $request): array
    {
        $dateFrom = null;
        $dateTo = null;

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->string('date_from')->toString())->startOfDay();
        }
        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->string('date_to')->toString())->endOfDay();
        }

        $baseQuery = DetectionResult::query();

        if ($dateFrom) {
            $baseQuery->where('detected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->where('detected_at', '<=', $dateTo);
        }

        $totalTraffic = (clone $baseQuery)->count();
        $normalTotal = (clone $baseQuery)->where('prediction', 0)->count();
        $malwareTotal = (clone $baseQuery)->where('prediction', 1)->count();

        $normalPercentage = $totalTraffic > 0 ? ($normalTotal / $totalTraffic) * 100 : 0;
        $malwarePercentage = $totalTraffic > 0 ? ($malwareTotal / $totalTraffic) * 100 : 0;

        // Top suspicious IPs
        $topSuspiciousIps = DetectionResult::query()
            ->select(
                'source_ip',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(confidence) as avg_confidence'),
                DB::raw("MIN(NULLIF(geo_src, '')) as geo_src")
            )
            ->where('prediction', 1)
            ->whereNotNull('source_ip')
            ->when($dateFrom, fn($q) => $q->where('detected_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('detected_at', '<=', $dateTo))
            ->groupBy('source_ip')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function (DetectionResult $ip) {
                $ip->setAttribute('location', $this->ipGeolocation->lookup($ip->source_ip, $ip->geo_src));
                return $ip;
            });

        // Daily stats (date, total, normal, malware)
        $dailyStats = DetectionResult::query()
            ->select(
                DB::raw('DATE(detected_at) as date'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN prediction = 0 THEN 1 ELSE 0 END) as normal_count'),
                DB::raw('SUM(CASE WHEN prediction = 1 THEN 1 ELSE 0 END) as malware_count')
            )
            ->when($dateFrom, fn($q) => $q->where('detected_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('detected_at', '<=', $dateTo))
            ->groupBy(DB::raw('DATE(detected_at)'))
            ->orderBy('date', 'desc')
            ->get();

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalTraffic' => $totalTraffic,
            'normalTotal' => $normalTotal,
            'malwareTotal' => $malwareTotal,
            'normalPercentage' => $normalPercentage,
            'malwarePercentage' => $malwarePercentage,
            'topSuspiciousIps' => $topSuspiciousIps,
            'dailyStats' => $dailyStats,
            'filters' => [
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'isPdf' => false,
        ];
    }
}
