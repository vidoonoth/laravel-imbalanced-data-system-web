<?php

use App\Models\DetectionResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function reportDetectionRecord(array $overrides = []): DetectionResult
{
    return DetectionResult::create(array_merge([
        'row_index' => 1,
        'detected_at' => Carbon::parse('2026-06-15 08:00:00'),
        'update_time' => Carbon::parse('2026-06-15 08:05:00'),
        'log_type' => 'traffic',
        'event_name' => 'Network Event',
        'disposition' => 'blocked',
        'protocol' => 'TCP',
        'source_ip' => '8.8.8.8',
        'destination_ip' => '172.16.0.5',
        'destination_port' => 443,
        'action' => 'deny',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.90,
        'geo_src' => 'US',
        'raw_record' => [],
    ], $overrides));
}

test('report top suspicious ips only uses public ips before applying limit', function () {
    Cache::flush();

    Http::fake(fn () => Http::response([
        'success' => false,
        'message' => 'Too Many Requests',
    ], 429));

    $user = User::factory()->create();

    foreach (range(1, 10) as $index) {
        foreach (range(1, 10 + $index) as $row) {
            reportDetectionRecord([
                'row_index' => ($index * 100) + $row,
                'source_ip' => "10.0.0.{$index}",
                'confidence' => 0.95,
                'geo_src' => 'IDN',
            ]);
        }
    }

    reportDetectionRecord([
        'row_index' => 999,
        'source_ip' => '8.8.8.8',
        'confidence' => 0.88,
        'geo_src' => 'US',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('report.index'));

    $response
        ->assertOk()
        ->assertViewHas('topSuspiciousIps', function ($ips) {
            return $ips->pluck('source_ip')->all() === ['8.8.8.8'];
        });
});
