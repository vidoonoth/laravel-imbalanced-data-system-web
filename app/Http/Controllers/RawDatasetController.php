<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\DatasetImport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawDatasetController extends Controller
{
    public function dashboard(Request $request): View
    {
        $datasets = Dataset::query()
            ->with('import')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $payloadColumns = $datasets
            ->getCollection()
            ->flatMap(fn (Dataset $dataset) => array_keys($dataset->payload ?? []))
            ->unique()
            ->take(8)
            ->values();

        $latestImport = DatasetImport::query()
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->latest('finished_at')
            ->latest('id')
            ->first();

        return view('dashboard-raw', [
            'totalRawRows' => Dataset::query()->count(),
            'completedImportCount' => DatasetImport::query()
                ->where('status', DatasetImport::STATUS_COMPLETED)
                ->count(),
            'failedImportCount' => DatasetImport::query()
                ->where('status', DatasetImport::STATUS_FAILED)
                ->count(),
            'latestImport' => $latestImport,
            'recentImports' => DatasetImport::query()
                ->latest('finished_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'datasets' => $datasets,
            'payloadColumns' => $payloadColumns,
        ]);
    }
}
