<?php

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Support\Facades\Hash;

function adminAccount(array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    AccessControl::assignAdminAccess($user);

    return $user;
}

test('regular user cannot access user management', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin can create network administrator user with limited feature access', function () {
    $admin = adminAccount();

    $this
        ->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Laporan')
        ->assertSee('User')
        ->assertSee('Hak Akses Menu');

    $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Operator Jaringan',
            'email' => 'operator@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => AccessControl::ROLE_USER,
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'operator@example.com')->firstOrFail();

    expect($user->hasRole(AccessControl::ROLE_USER))->toBeTrue()
        ->and($user->can('dashboard.view'))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_RAW))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD))->toBeTrue()
        ->and($user->can('report.view'))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_MANAGE_USERS))->toBeFalse();

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('admin permission validation errors render without crashing', function () {
    $admin = adminAccount();
    $user = User::factory()->create();
    AccessControl::assignDefaultUserAccess($user);

    $this
        ->actingAs($admin)
        ->followingRedirects()
        ->from(route('admin.permissions.edit', $user))
        ->put(route('admin.permissions.update', $user), [
            'permissions' => ['dataset-analysis.view'],
        ])
        ->assertOk()
        ->assertSee('The selected permissions.0 is invalid.');
});

test('permission index shows readable access names', function () {
    $admin = adminAccount();

    $this
        ->actingAs($admin)
        ->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertSee('dashboard')
        ->assertSee('dashboard hasil deteksi')
        ->assertSee('report')
        ->assertSee('kelola data user')
        ->assertSee('kelola hak akses menu')
        ->assertSee('deteksi')
        ->assertSee('ip mencurigakan')
        ->assertDontSee('dashboard.view')
        ->assertDontSee('dashboard.detection.view')
        ->assertDontSee('dashboard.raw.view')
        ->assertDontSee('report.view')
        ->assertDontSee('users.manage')
        ->assertDontSee('permissions.manage')
        ->assertDontSee('dashboard.detection-card.view')
        ->assertDontSee('dashboard.suspicious-ip-card.view');
});

test('admin can manage dashboard card permissions', function () {
    $admin = adminAccount();
    $user = User::factory()->create();
    AccessControl::assignDefaultUserAccess($user);

    $this
        ->actingAs($admin)
        ->get(route('admin.permissions.edit', $user))
        ->assertOk()
        ->assertSeeInOrder(['Monitoring', 'Administrasi', 'Laporan'])
        ->assertSee('Dashboard')
        ->assertSee('Dashboard Hasil Deteksi')
        ->assertSee('Dashboard Raw Data')
        ->assertSee('Laporan')
        ->assertSee('Deteksi Malware')
        ->assertSee('IP Mencurigakan')
        ->assertDontSee('Menjalankan deteksi malware dari file CSV.')
        ->assertDontSee('Melihat riwayat dan detail hasil deteksi.');

    expect(AccessControl::permissions()['report.view']['group'])->toBe('Laporan');

    $this
        ->actingAs($admin)
        ->put(route('admin.permissions.update', $user), [
            'permissions' => [
                'dashboard.view',
                AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD,
            ],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    $user->refresh();

    expect($user->can('dashboard.view'))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_RAW))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD))->toBeFalse();

    $this
        ->actingAs($admin)
        ->put(route('admin.permissions.update', $user), [
            'permissions' => [
                AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD,
            ],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    $user->refresh();

    expect($user->can('dashboard.view'))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_RAW))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD))->toBeFalse();
});

test('dashboard raw access is exclusive from detection dashboard access', function () {
    $admin = adminAccount();
    $user = User::factory()->create();
    AccessControl::assignDefaultUserAccess($user);

    $this
        ->actingAs($admin)
        ->put(route('admin.permissions.update', $user), [
            'permissions' => [
                'dashboard.view',
                AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION,
                AccessControl::PERMISSION_VIEW_DASHBOARD_RAW,
                AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD,
                AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD,
            ],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    $user->refresh();

    expect($user->can('dashboard.view'))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_RAW))->toBeTrue()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD))->toBeFalse();

    $this
        ->actingAs($user)
        ->get(route('dashboard.raw'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('admin role feature access can be customized', function () {
    $admin = adminAccount();
    $targetAdmin = adminAccount([
        'name' => 'Admin Terbatas',
        'email' => 'admin-terbatas@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->put(route('admin.permissions.update', $targetAdmin), [
            'permissions' => ['dashboard.view'],
        ])
        ->assertRedirect(route('admin.permissions.index'));

    $targetAdmin->refresh();

    expect($targetAdmin->hasRole(AccessControl::ROLE_ADMIN))->toBeTrue()
        ->and($targetAdmin->can(AccessControl::PERMISSION_MANAGE_USERS))->toBeTrue()
        ->and($targetAdmin->can('dashboard.view'))->toBeTrue()
        ->and($targetAdmin->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION))->toBeTrue()
        ->and($targetAdmin->can(AccessControl::PERMISSION_VIEW_DASHBOARD_RAW))->toBeFalse()
        ->and($targetAdmin->can(AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD))->toBeFalse()
        ->and($targetAdmin->can(AccessControl::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD))->toBeFalse()
        ->and($targetAdmin->can('report.view'))->toBeFalse();
});
