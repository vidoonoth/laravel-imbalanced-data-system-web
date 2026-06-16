<?php

use App\Models\DetectionResult;
use Illuminate\Support\Facades\Config;

test('detection api stores ml pipeline results', function () {
    Config::set('services.ml_pipeline.api_key', 'test-ml-key');

    $response = $this
        ->withToken('test-ml-key')
        ->postJson('/api/detection/results', [
            'results' => [
                [
                    'row_index' => 10,
                    'update_time' => '2026-04-01 00:00:01',
                    'sn' => 'D043027CCA4FF',
                    'log_type' => 'Traffic',
                    'log' => 'FWDeny, Denied, disp=Deny, protocol=https/tcp',
                    'event_name' => 'FWDeny',
                    'disposition' => 'Deny',
                    'priority' => 4,
                    'protocol' => 'https/tcp',
                    'source_ip' => '177.152.98.175',
                    'destination_ip' => '103.149.71.15',
                    'source_port' => 63433,
                    'destination_port' => 443,
                    'source_interface' => 'Ekternal1',
                    'destination_interface' => 'Firebox',
                    'policy' => 'Unhandled External Packet-00',
                    'pckt_len' => 60,
                    'ttl' => 121,
                    'sent_bytes' => 60,
                    'rcvd_bytes' => 0,
                    'geo_src' => 'BRA',
                    'geo_dst' => 'IDN',
                    'prediction' => 1,
                    'prediction_label' => 'Malware',
                    'confidence' => 0.97,
                    'probability_normal' => 0.03,
                    'probability_attack' => 0.97,
                    'detected_at' => '2026-06-15 08:00:00',
                    'raw_record' => [
                        'source_file' => 'dataset.csv',
                    ],
                ],
            ],
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success');

    $record = DetectionResult::query()->first();

    expect($record)
        ->not->toBeNull()
        ->and($record->source_ip)->toBe('177.152.98.175')
        ->and($record->prediction)->toBe(1)
        ->and($record->prediction_label)->toBe('Malware')
        ->and((float) $record->probability_attack)->toBe(0.97)
        ->and($record->raw_record['source_file'])->toBe('dataset.csv');
});

test('detection api rejects an invalid api key', function () {
    Config::set('services.ml_pipeline.api_key', 'test-ml-key');

    $this
        ->withToken('wrong-key')
        ->postJson('/api/detection/results', [
            'results' => [
                ['prediction' => 1],
            ],
        ])
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error');
});
