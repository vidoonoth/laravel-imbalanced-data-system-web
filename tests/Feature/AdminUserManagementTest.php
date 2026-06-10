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
        ->assertSee('Detection')
        ->assertSee('Riwayat Deteksi')
        ->assertSee('Manajemen User')
        ->assertSee('value="dashboard.view"', false)
        ->assertSee('value="detection.run"', false)
        ->assertSee('value="detection-history.view"', false)
        ->assertSee('value="users.manage"', false)
        ->assertDontSee('Aktivitas IP')
        ->assertDontSee('Dataset Analysis')
        ->assertDontSee('Imbalance Handling')
        ->assertDontSee('Evaluation')
        ->assertDontSee('Reports');

    $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Operator Jaringan',
            'email' => 'operator@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => AccessControl::ROLE_USER,
            'permissions' => ['detection.run'],
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'operator@example.com')->firstOrFail();

    expect($user->hasRole(AccessControl::ROLE_USER))->toBeTrue()
        ->and($user->can('detection.run'))->toBeTrue()
        ->and($user->can('dashboard.view'))->toBeFalse()
        ->and($user->can(AccessControl::PERMISSION_MANAGE_USERS))->toBeFalse();

    $this
        ->actingAs($user)
        ->get(route('detection'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('admin permission validation errors render without crashing', function () {
    $admin = adminAccount();

    $this
        ->actingAs($admin)
        ->followingRedirects()
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'Operator Validasi',
            'email' => 'operator-validasi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => AccessControl::ROLE_USER,
            'permissions' => ['dataset-analysis.view'],
        ])
        ->assertOk()
        ->assertSee('The selected permissions.0 is invalid.');
});

test('admin can update feature access without changing password', function () {
    $admin = adminAccount();
    $user = User::factory()->create([
        'name' => 'Operator Hak Akses',
        'email' => 'akses@example.com',
        'password' => Hash::make('old-password'),
    ]);

    AccessControl::assignDefaultUserAccess($user);

    $this
        ->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Operator Hak Akses',
            'email' => 'akses@example.com',
            'role' => AccessControl::ROLE_USER,
            'permissions' => ['dashboard.view'],
            'password' => 'browser-autofill-value',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user->refresh();

    expect($user->can('dashboard.view'))->toBeTrue()
        ->and($user->can('detection.run'))->toBeFalse()
        ->and(Hash::check('old-password', $user->password))->toBeTrue()
        ->and(Hash::check('browser-autofill-value', $user->password))->toBeFalse();
});

test('admin role feature access can be customized', function () {
    $admin = adminAccount();
    $targetAdmin = adminAccount([
        'name' => 'Admin Terbatas',
        'email' => 'admin-terbatas@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->put(route('admin.users.update', $targetAdmin), [
            'name' => 'Admin Terbatas',
            'email' => 'admin-terbatas@example.com',
            'role' => AccessControl::ROLE_ADMIN,
            'permissions' => ['detection.run'],
        ])
        ->assertRedirect(route('admin.users.index'));

    $targetAdmin->refresh();

    expect($targetAdmin->hasRole(AccessControl::ROLE_ADMIN))->toBeTrue()
        ->and($targetAdmin->can(AccessControl::PERMISSION_MANAGE_USERS))->toBeTrue()
        ->and($targetAdmin->can('detection.run'))->toBeTrue()
        ->and($targetAdmin->can('dashboard.view'))->toBeFalse()
        ->and($targetAdmin->can('detection-history.view'))->toBeFalse();
});

test('admin cannot demote the active account', function () {
    $admin = adminAccount([
        'name' => 'Root Admin',
        'email' => 'root@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.users.edit', $admin))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Detection')
        ->assertSee('Riwayat Deteksi')
        ->assertSee('Manajemen User')
        ->assertSee('value="dashboard.view"', false)
        ->assertSee('value="detection.run"', false)
        ->assertSee('value="detection-history.view"', false)
        ->assertSee('value="users.manage"', false)
        ->assertDontSee('Aktivitas IP')
        ->assertDontSee('Dataset Analysis')
        ->assertDontSee('Imbalance Handling')
        ->assertDontSee('Evaluation')
        ->assertDontSee('Reports');

    $this
        ->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => 'Root Admin',
            'email' => 'root@example.com',
            'password' => null,
            'password_confirmation' => null,
            'role' => AccessControl::ROLE_USER,
            'permissions' => AccessControl::defaultUserPermissions(),
        ])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->hasRole(AccessControl::ROLE_ADMIN))->toBeTrue();
});
