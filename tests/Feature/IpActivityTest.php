<?php

use App\Models\DetectionResult;
use App\Models\User;
use App\Support\AccessControl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function ipActivityRecord(array $overrides = []): DetectionResult
{
    return DetectionResult::create(array_merge([
        'row_index' => 1,
        'update_time' => Carbon::parse('2026-05-15 08:10:00'),
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

test('ip activity page shows shared activity details', function () {
    $user = User::factory()->create();

    ipActivityRecord([
        'row_index' => 1,
        'update_time' => Carbon::parse('2026-05-15 08:15:00'),
        'event_name' => 'Port Scan',
        'disposition' => 'blocked',
        'action' => 'deny',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.91,
        'raw_record' => [
            'url' => '/admin',
            'status_code' => 403,
        ],
    ]);

    ipActivityRecord([
        'row_index' => 2,
        'update_time' => Carbon::parse('2026-05-15 09:20:00'),
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

    ipActivityRecord([
        'source_ip' => '10.10.10.10',
        'event_name' => 'Data Exfiltration',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.99,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.ip-activity', ['ip' => '10.10.10.10']));

    $response
        ->assertOk()
        ->assertSee('Detail Aktivitas IP')
        ->assertSee('10.10.10.10')
        ->assertSee('Port Scan')
        ->assertSee('Login Attempt')
        ->assertSee('/admin')
        ->assertSee('403')
        ->assertSee('blocked')
        ->assertSee('Malware')
        ->assertSee('Normal')
        ->assertSee('Data Exfiltration')
        ->assertSee('Lihat Lokasi')
        ->assertSee(route('dashboard.ip-location', ['ip' => '10.10.10.10']), false);
});

test('ip activity page shows activity from another users scan', function () {
    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '10.20.30.50',
        'geo_src' => 'IDN',
        'event_name' => 'Other User Alert',
        'prediction' => 1,
        'prediction_label' => 'Malware',
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard.ip-activity', ['ip' => '10.20.30.50']))
        ->assertOk()
        ->assertSee('10.20.30.50')
        ->assertSee('Other User Alert');
});

test('ip activity page returns not found without an existing ip', function () {
    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '203.0.113.10',
        'prediction' => 1,
        'prediction_label' => 'Malware',
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard.ip-activity'))
        ->assertNotFound();

    $this
        ->actingAs($user)
        ->get(route('dashboard.ip-activity', ['ip' => '203.0.113.99']))
        ->assertNotFound();

    $this
        ->actingAs($user)
        ->get(route('dashboard.ip-location'))
        ->assertNotFound();

    $this
        ->actingAs($user)
        ->get(route('dashboard.ip-location', ['ip' => '203.0.113.99']))
        ->assertNotFound();
});

test('dashboard shows shared detection data from another user', function () {
    Http::fake(fn () => Http::response(['success' => false], 429));

    $viewer = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '8.8.4.4',
        'geo_src' => 'IDN',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.88,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('8.8.4.4')
        ->assertSee('Top IP Mencurigakan')
        ->assertSee('Deteksi Terakhir');
});

test('dashboard shows detail link for top suspicious ip', function () {
    Http::fake(fn () => Http::response(['success' => false], 429));

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '8.8.8.8',
        'geo_src' => 'IDN',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.88,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Top IP Mencurigakan')
        ->assertSee('8.8.8.8')
        ->assertSee('Detail')
        ->assertSee('Lihat Lokasi')
        ->assertSee(route('dashboard.ip-activity', ['ip' => '8.8.8.8']), false)
        ->assertSee(route('dashboard.ip-location', ['ip' => '8.8.8.8']), false);
});

test('dashboard hides cards without dashboard card permissions', function () {
    $user = User::factory()->create();
    $user->syncPermissions([
        'dashboard.view',
        AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION,
    ]);

    ipActivityRecord([
        'source_ip' => '10.20.30.25',
        'geo_src' => 'IDN',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.88,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertDontSee('data-dashboard-card="detection"', false)
        ->assertDontSee('data-dashboard-card="detection-summary"', false)
        ->assertDontSee('data-dashboard-card="detection-chart"', false)
        ->assertDontSee('data-dashboard-card="suspicious-ip"', false)
        ->assertDontSee('data-dashboard-card="suspicious-ip-list"', false);

    $user->syncPermissions([
        'dashboard.view',
        AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION,
        AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('data-dashboard-card="detection"', false)
        ->assertSee('data-dashboard-card="detection-summary"', false)
        ->assertSee('data-dashboard-card="detection-chart"', false)
        ->assertDontSee('data-dashboard-card="suspicious-ip"', false)
        ->assertDontSee('data-dashboard-card="suspicious-ip-list"', false);
});

test('dashboard shows api geolocation for top suspicious ip', function () {
    Cache::flush();

    Http::fake(fn ($request) => Http::response([
        'success' => true,
        'country' => 'United States',
        'country_code' => 'US',
        'region' => 'California',
        'city' => 'Mountain View',
    ]));

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '8.8.8.8',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.92,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('8.8.8.8')
        ->assertSee('Lokasi: United States, California, Mountain View')
        ->assertSee('GeoIP');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://ipwho.is/8.8.8.8'));
});

test('ip location page shows api geolocation', function () {
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

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '1.1.1.1',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.89,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.ip-location', ['ip' => '1.1.1.1']));

    $response
        ->assertOk()
        ->assertSee('Lokasi IP Mencurigakan')
        ->assertSee('Lokasi Asal IP')
        ->assertSee('Australia, Queensland, South Brisbane')
        ->assertSee('data GeoIP')
        ->assertSee('-27.475000, 153.013000')
        ->assertSee('Buka OpenStreetMap')
        ->assertSee('openstreetmap.org/export/embed.html', false);
});

test('ip location refreshes cached api location without coordinates', function () {
    Cache::flush();

    $ipAddress = '1.0.0.1';

    Cache::put('ip_geolocation:' . sha1($ipAddress), [
        'label' => 'Australia, Queensland',
        'country' => 'Australia',
        'country_code' => 'AU',
        'region' => 'Queensland',
        'city' => null,
        'source' => 'api',
    ], now()->addDay());

    Http::fake(fn ($request) => Http::response([
        'success' => true,
        'country' => 'Australia',
        'country_code' => 'AU',
        'region' => 'New South Wales',
        'city' => 'Sydney',
        'latitude' => -33.8688,
        'longitude' => 151.2093,
    ]));

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => $ipAddress,
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.87,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.ip-location', ['ip' => $ipAddress]));

    $response
        ->assertOk()
        ->assertSee('Australia, New South Wales, Sydney')
        ->assertSee('-33.868800, 151.209300')
        ->assertSee('Buka OpenStreetMap');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://ipwho.is/1.0.0.1'));
});

test('dashboard hides private suspicious ip without api request', function () {
    Cache::flush();
    Http::fake();

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '10.10.10.10',
        'geo_src' => 'IDN',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.86,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Top IP Mencurigakan')
        ->assertSee('Belum ada IP dengan prediksi malware.')
        ->assertDontSee('10.10.10.10')
        ->assertDontSee('Lokasi: IDN');

    Http::assertNothingSent();
});

test('geolocation api failure keeps ip location page available', function () {
    Cache::flush();

    Http::fake(fn ($request) => Http::response([
        'success' => false,
        'message' => 'Too Many Requests',
    ], 429));

    $user = User::factory()->create();

    ipActivityRecord([
        'source_ip' => '9.9.9.9',
        'prediction' => 1,
        'prediction_label' => 'Malware',
        'confidence' => 0.83,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard.ip-location', ['ip' => '9.9.9.9']));

    $response
        ->assertOk()
        ->assertSee('Lokasi IP Mencurigakan')
        ->assertSee('Lokasi Asal IP')
        ->assertSee('Lokasi tidak tersedia')
        ->assertSee('belum tersedia')
        ->assertSee('Peta belum tersedia karena data koordinat tidak ditemukan untuk IP ini.');
});
