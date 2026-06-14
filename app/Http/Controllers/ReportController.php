<?php

namespace App\Http\Controllers;

use App\Models\DetectionResult;
use App\Models\DetectionScan;
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
        
        $scanQuery = DetectionScan::query()
            ->with('user')
            ->where('status', 'success')
            ->latest('completed_at');
            
        if ($dateFrom) {
            $scanQuery->where('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $scanQuery->where('completed_at', '<=', $dateTo);
        }
        
        $data['recentScans'] = $scanQuery->paginate(10)->withQueryString();

        return view('report.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        
        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];
        
        $scanQuery = DetectionScan::query()
            ->with('user')
            ->where('status', 'success')
            ->latest('completed_at');
            
        if ($dateFrom) {
            $scanQuery->where('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $scanQuery->where('completed_at', '<=', $dateTo);
        }
        
        $data['recentScans'] = $scanQuery->limit(200)->get();
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

        $scanQuery = DetectionScan::query()
            ->where('status', 'success');

        if ($dateFrom) {
            $scanQuery->where('completed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $scanQuery->where('completed_at', '<=', $dateTo);
        }

        $totalScans = (clone $scanQuery)->count();
        $totalTraffic = (int) (clone $scanQuery)->sum('total_samples');
        $normalTotal = (int) (clone $scanQuery)->sum('normal_count');
        $malwareTotal = (int) (clone $scanQuery)->sum('attack_count');

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
            ->whereHas('scan', function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'success');
                if ($dateFrom) {
                    $query->where('completed_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->where('completed_at', '<=', $dateTo);
                }
            })
            ->groupBy('source_ip')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function (DetectionResult $ip) {
                $ip->setAttribute('location', $this->ipGeolocation->lookup($ip->source_ip, $ip->geo_src));
                return $ip;
            });

        // User stats
        $userStats = DetectionScan::query()
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_scans'),
                DB::raw('SUM(total_samples) as total_samples'),
                DB::raw('SUM(attack_count) as total_malware')
            )
            ->where('status', 'success')
            ->when($dateFrom, fn($q) => $q->where('completed_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('completed_at', '<=', $dateTo))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalScans' => $totalScans,
            'totalTraffic' => $totalTraffic,
            'normalTotal' => $normalTotal,
            'malwareTotal' => $malwareTotal,
            'normalPercentage' => $normalPercentage,
            'malwarePercentage' => $malwarePercentage,
            'topSuspiciousIps' => $topSuspiciousIps,
            'userStats' => $userStats,
            'filters' => [
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'isPdf' => false,
        ];
    }
}
