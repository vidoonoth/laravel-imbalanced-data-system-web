<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatasetImport extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'source_fingerprint',
        'source_host',
        'source_path',
        'source_filename',
        'size_bytes',
        'last_modified_at',
        'checksum_sha256',
        'status',
        'rows_imported',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'last_modified_at' => 'datetime',
            'rows_imported' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class);
    }
}
