<?php

use App\Models\DetectionResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

function apiDetectionRecord(array $overrides = []): DetectionResult
{
    return DetectionResult::create(array_merge([
        'row_index' => 1,
        'detected_at' => Carbon::parse('2026-06-15 08:00:00'),
        'update_time' => Carbon::parse('2026-06-15 08:05:00'),
        'log_type' => 'traffic',
        'event_name' => 'Network Event',
        'disposition' => 'allowed',
        'protocol' => 'TCP',
        'source_ip' => '10.10.10.10',
        'destination_ip' => '172.16.0.5',
        'destination_port' => 443,
        'action' => 'allow',
        'prediction' => 0,
        'prediction_label' => 'Normal',
        'confidence' => 0.25,
        'raw_record' => [],
    ], $overrides));
}

test('detection api rejects an invalid api key', function () {
    Config::set('services.ml.api_key', 'test-ml-key');

    $this
        ->withToken('wrong-key')
        ->getJson('/api/dashboard')
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error');
});

test('dashboard api returns detection summary and suspicious ip data', function () {
    Config::set('services.ml.api_key', 'test-ml-key');
    Cache::flush();
    Http::fake(fn ($request) => Http::response([
        'success' => true,
        'country' => 'United States',
        'country_code' => 'US',
        'region' => 'California',
        'city' => 'Mountain View',
        'latitude' => 37.386,
        'longitude' => -122.084,
    ]));

    apiDetectionRecord([
        'source_ip' => '8.8.8.8',
        'event_name' => 'Port Scan',
        'disposition' => 'blocked',
        'action' => 'deny',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.92,
        'raw_record' => ['url' => '/admin'],
    ]);

    apiDetectionRecord([
        'source_ip' => '10.10.10.10',
        'prediction' => 0,
        'prediction_label' => 'Normal',
        'confidence' => 0.12,
    ]);

    $response = $this
        ->withToken('test-ml-key')
        ->getJson('/api/dashboard');

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.summary.total_traffic', 2)
        ->assertJsonPath('data.summary.malware_total', 1)
        ->assertJsonPath('data.summary.normal_total', 1)
        ->assertJsonPath('data.summary.suspicious_ip_count', 1)
        ->assertJsonPath('data.suspicious_ips.0.source_ip', '8.8.8.8')
        ->assertJsonPath('data.suspicious_ips.0.location.label', 'United States, California, Mountain View')
        ->assertJsonPath('data.recent_detections.0.source_ip', '10.10.10.10');
});

test('suspicious ip detail api returns activity breakdown', function () {
    Config::set('services.ml.api_key', 'test-ml-key');

    apiDetectionRecord([
        'source_ip' => '203.0.113.25',
        'destination_ip' => '192.0.2.10',
        'destination_port' => 443,
        'protocol' => 'TCP',
        'event_name' => 'Port Scan',
        'disposition' => 'blocked',
        'action' => 'deny',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.94,
        'raw_record' => [
            'url' => '/admin',
            'status_code' => 403,
        ],
    ]);

    apiDetectionRecord([
        'source_ip' => '203.0.113.25',
        'destination_ip' => '192.0.2.20',
        'destination_port' => 80,
        'protocol' => 'HTTP',
        'event_name' => 'Login Attempt',
        'disposition' => 'allowed',
        'action' => 'allow',
        'prediction' => 0,
        'prediction_label' => 'Normal',
        'confidence' => 0.20,
        'raw_record' => [
            'url' => '/login',
            'status_code' => 200,
        ],
    ]);

    $response = $this
        ->withToken('test-ml-key')
        ->getJson('/api/dashboard/suspicious-ips/detail?ip=203.0.113.25');

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.ip', '203.0.113.25')
        ->assertJsonPath('data.summary.total_activities', 2)
        ->assertJsonPath('data.summary.total_alerts', 1)
        ->assertJsonPath('data.top_events.0.label', 'Login Attempt')
        ->assertJsonPath('data.top_endpoints.0.label', '/admin')
        ->assertJsonPath('data.top_response_statuses.0.label', '403')
        ->assertJsonPath('data.alerts.0.event_name', 'Port Scan')
        ->assertJsonPath('data.activities.total', 2);
});

test('suspicious ip location api returns geolocation summary', function () {
    Config::set('services.ml.api_key', 'test-ml-key');
    Cache::flush();
    Http::fake(fn ($request) => Http::response([
        'success' => true,
        'country' => 'Australia',
        'country_code' => 'AU',
        'region' => 'Queensland',
        'city' => 'South Brisbane',
        'latitude' => -27.475,
        'longitude' => 153.013,
    ]));

    apiDetectionRecord([
        'source_ip' => '1.1.1.1',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.89,
    ]);

    $response = $this
        ->withToken('test-ml-key')
        ->getJson('/api/dashboard/suspicious-ips/location?ip=1.1.1.1');

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.ip', '1.1.1.1')
        ->assertJsonPath('data.location.label', 'Australia, Queensland, South Brisbane')
        ->assertJsonPath('data.location.latitude', -27.475)
        ->assertJsonPath('data.location.longitude', 153.013)
        ->assertJsonPath('data.summary.total_alerts', 1)
        ->assertJsonPath('data.summary.latest_alert.source_ip', '1.1.1.1');
});

test('suspicious ip api returns not found for unknown ip', function () {
    Config::set('services.ml.api_key', 'test-ml-key');

    $this
        ->withToken('test-ml-key')
        ->getJson('/api/dashboard/suspicious-ips/detail?ip=203.0.113.99')
        ->assertNotFound()
        ->assertJsonPath('status', 'error');
});
