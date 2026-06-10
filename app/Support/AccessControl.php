<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControl
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const PERMISSION_MANAGE_USERS = 'users.manage';

    /**
     * @return array<string, array{label: string, group: string, description: string}>
     */
    public static function permissions(): array
    {
        return [
            'dashboard.view' => [
                'label' => 'Dashboard',
                'group' => 'Monitoring',
                'description' => 'Melihat ringkasan deteksi dan statistik traffic.',
            ],
            'detection.run' => [
                'label' => 'Detection',
                'group' => 'Deteksi',
                'description' => 'Menjalankan deteksi malware dari file CSV.',
            ],
            'detection-history.view' => [
                'label' => 'Riwayat Deteksi',
                'group' => 'Deteksi',
                'description' => 'Melihat riwayat dan detail hasil deteksi.',
            ],
            self::PERMISSION_MANAGE_USERS => [
                'label' => 'Manajemen User',
                'group' => 'Administrasi',
                'description' => 'Membuat, mengubah, menghapus user, role, dan hak akses.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return array_keys(self::permissions());
    }

    /**
     * @return list<string>
     */
    public static function defaultUserPermissions(): array
    {
        return array_values(array_diff(self::permissionNames(), [self::PERMISSION_MANAGE_USERS]));
    }

    /**
     * @return list<string>
     */
    public static function defaultAdminPermissions(): array
    {
        return self::defaultUserPermissions();
    }

    /**
     * @return list<string>
     */
    public static function roles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_USER];
    }

    public static function ensureRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::permissionNames())
            ->delete();

        foreach (self::permissionNames() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate(self::ROLE_USER, 'web')->syncPermissions([]);
        Role::findOrCreate(self::ROLE_ADMIN, 'web')->syncPermissions([self::PERMISSION_MANAGE_USERS]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function assignDefaultUserAccess(User $user): void
    {
        self::ensureRolesAndPermissions();

        $user->syncRoles([self::ROLE_USER]);
        $user->syncPermissions(self::defaultUserPermissions());
    }

    public static function assignAdminAccess(User $user): void
    {
        self::ensureRolesAndPermissions();

        $user->syncRoles([self::ROLE_ADMIN]);
        $user->syncPermissions(self::defaultAdminPermissions());
    }

    public static function homeRouteNameFor(User $user): string
    {
        $routesByPermission = [
            'dashboard.view' => 'dashboard',
            'detection.run' => 'detection',
            'detection-history.view' => 'detection.history',
            self::PERMISSION_MANAGE_USERS => 'admin.users.index',
        ];

        foreach ($routesByPermission as $permission => $routeName) {
            if ($user->can($permission)) {
                return $routeName;
            }
        }

        return 'profile.edit';
    }
}
