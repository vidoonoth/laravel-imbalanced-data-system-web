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
        // Collect all unique payload keys from completed imports for column headers
        $allPayloadColumns = Dataset::query()
            ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
            ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
            ->limit(100)
            ->get(['datasets.payload'])
            ->flatMap(fn (Dataset $dataset) => array_keys($dataset->payload ?? []))
            ->unique()
            ->values();

        // Build query with optional filters
        $datasetsQuery = Dataset::query()
            ->with('import')
            ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
            ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
            ->select('datasets.*');

        // Filter by import file
        if ($request->filled('file')) {
            $datasetsQuery->where('datasets.dataset_import_id', $request->integer('file'));
        }

        // Search within payload fields
        if ($request->filled('search')) {
            $searchTerm = $request->string('search')->toString();
            $datasetsQuery->where(function ($query) use ($searchTerm, $allPayloadColumns) {
                foreach ($allPayloadColumns as $column) {
                    $query->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(datasets.payload, ?)) LIKE ?",
                        ['$.' . $column, '%' . $searchTerm . '%']
                    );
                }
            });
        }

        $datasets = $datasetsQuery
            ->orderBy('datasets.dataset_import_id', 'asc')
            ->orderBy('datasets.row_number', 'asc')
            ->paginate(50)
            ->withQueryString();

        $latestImport = DatasetImport::query()
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->latest('finished_at')
            ->latest('id')
            ->first();

        $completedImports = DatasetImport::query()
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->orderBy('source_filename', 'asc')
            ->get(['id', 'source_filename', 'rows_imported']);

        return view('dashboard-raw', [
            'totalRawRows' => Dataset::query()
                ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
                ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
                ->count(),
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
            'payloadColumns' => $allPayloadColumns,
            'completedImports' => $completedImports,
            'filters' => [
                'file' => $request->integer('file'),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }
}
