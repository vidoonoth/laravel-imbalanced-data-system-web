<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionResult extends Model
{
    protected $table = 'detection_records';

    protected $fillable = [
        'detection_scan_id',
        'row_index',
        'update_time',
        'sn',
        'log_type',
        'log',
        'event_name',
        'disposition',
        'priority',
        'protocol',
        'source_ip',
        'destination_ip',
        'source_port',
        'destination_port',
        'source_interface',
        'destination_interface',
        'policy',
        'pckt_len',
        'ttl',
        'sent_bytes',
        'rcvd_bytes',
        'geo_src',
        'geo_dst',
        'action',
        'prediction',
        'prediction_label',
        'confidence',
        'probability_normal',
        'probability_attack',
        'raw_record',
    ];

    protected function casts(): array
    {
        return [
            'update_time' => 'datetime',
            'priority' => 'integer',
            'source_port' => 'integer',
            'destination_port' => 'integer',
            'pckt_len' => 'integer',
            'ttl' => 'integer',
            'sent_bytes' => 'integer',
            'rcvd_bytes' => 'integer',
            'prediction' => 'integer',
            'confidence' => 'decimal:6',
            'probability_normal' => 'decimal:6',
            'probability_attack' => 'decimal:6',
            'raw_record' => 'array',
        ];
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(DetectionScan::class, 'detection_scan_id');
    }
}
