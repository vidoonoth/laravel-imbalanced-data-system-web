<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\Http\Request;

class DatasetApiController extends Controller
{
    public function pending(Request $request)
    {
        $limit = $this->limitFromRequest($request, 'limit', 500, 1000);

        $items = Dataset::query()
            ->with('import')
            ->whereDoesntHave('detectionResult')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (Dataset $dataset) => [
                'id' => $dataset->id,
                'dataset_import_id' => $dataset->dataset_import_id,
                'row_number' => $dataset->row_number,
                'row_hash' => $dataset->row_hash,
                'payload' => $dataset->payload ?? [],
                'source' => [
                    'filename' => $dataset->import?->source_filename,
                    'path' => $dataset->import?->source_path,
                    'host' => $dataset->import?->source_host,
                ],
            ])
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items,
                'count' => $items->count(),
                'remaining' => Dataset::query()
                    ->whereDoesntHave('detectionResult')
                    ->count(),
            ],
        ]);
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
