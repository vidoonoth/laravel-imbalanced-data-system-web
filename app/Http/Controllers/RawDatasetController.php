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
        // mengambil semua kolom payload dari dataset yang telah diimpor
        $allPayloadColumns = Dataset::query()
            ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
            ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
            ->limit(100)
            ->get(['datasets.payload'])
            ->flatMap(fn (Dataset $dataset) => array_keys($dataset->payload ?? []))
            ->unique()
            ->values();

        // membuat query untuk mengambil dataset yang telah diimpor dan selesai
        $datasetsQuery = Dataset::query()
            ->with('import')
            ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
            ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
            ->select('datasets.*');

        // filter berdasarkan file import jika parameter 'file' ada pada request
        if ($request->filled('file')) {
            $datasetsQuery->where('datasets.dataset_import_id', $request->integer('file'));
        }

        // filter berdasarkan pencarian jika parameter 'search' ada pada request
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

        // mengambil dataset yang telah difilter, diurutkan berdasarkan dataset_import_id dan row_number, dan dipaginasi
        $datasets = $datasetsQuery
            ->orderBy('datasets.dataset_import_id', 'asc')
            ->orderBy('datasets.row_number', 'asc')
            ->paginate(10)
            ->withQueryString();

        // mengambil import dataset terbaru yang telah selesai
        $latestImport = DatasetImport::query()
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->latest('finished_at')
            ->latest('id')
            ->first();

        // mengambil semua import dataset yang telah selesai, diurutkan berdasarkan source_filename
        $completedImports = DatasetImport::query()
            ->where('status', DatasetImport::STATUS_COMPLETED)
            ->orderBy('source_filename', 'asc')
            ->get(['id', 'source_filename', 'rows_imported']);

        // mengembalikan view dashboard-raw dengan data yang telah dikumpulkan
        return view('dashboard-raw', [
            // menghitung total baris dataset dari semua import yang telah selesai
            'totalRawRows' => Dataset::query()
                ->join('dataset_imports', 'datasets.dataset_import_id', '=', 'dataset_imports.id')
                ->where('dataset_imports.status', DatasetImport::STATUS_COMPLETED)
                ->count(),
            // menghitung jumlah import dataset yang telah selesai
            'completedImportCount' => DatasetImport::query()
                ->where('status', DatasetImport::STATUS_COMPLETED)
                ->count(),
            // menghitung jumlah import dataset yang gagal
            'failedImportCount' => DatasetImport::query()
                ->where('status', DatasetImport::STATUS_FAILED)
                ->count(),
            // menghitung jumlah import dataset yang sedang berjalan
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
