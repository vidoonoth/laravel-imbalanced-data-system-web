<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dataset extends Model
{
    protected $fillable = [
        'dataset_import_id',
        'row_number',
        'row_hash',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(DatasetImport::class, 'dataset_import_id');
    }

    public function detectionResult(): HasOne
    {
        return $this->hasOne(DetectionResult::class);
    }
}
