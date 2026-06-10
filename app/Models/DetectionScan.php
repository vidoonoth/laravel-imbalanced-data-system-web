<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetectionScan extends Model
{
    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_path',
        'file_size',
        'mime_type',
        'status',
        'total_samples',
        'normal_count',
        'attack_count',
        'normal_percentage',
        'attack_percentage',
        'raw_summary',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_samples' => 'integer',
            'normal_count' => 'integer',
            'attack_count' => 'integer',
            'normal_percentage' => 'float',
            'attack_percentage' => 'float',
            'raw_summary' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(DetectionResult::class);
    }
}
