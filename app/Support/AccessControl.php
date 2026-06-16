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

    public const PERMISSION_MANAGE_PERMISSIONS = 'permissions.manage';

    public const PERMISSION_VIEW_DASHBOARD_DETECTION_CARD = 'dashboard.detection-card.view';

    public const PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD = 'dashboard.suspicious-ip-card.view';

    /**
     * @return array<string, array{label: string, group: string, description: string, parent?: string}>
     */
    public static function permissions(): array
    {
        return [
            'dashboard.view' => [
                'label' => 'Dashboard',
                'group' => 'Monitoring',
                'description' => 'Melihat ringkasan deteksi dan statistik traffic.',
            ],
            self::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD => [
                'label' => 'Deteksi Malware',
                'group' => 'Monitoring',
                'parent' => 'dashboard.view',
                'description' => 'Menampilkan card dan chart deteksi malware pada dashboard.',
            ],
            self::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD => [
                'label' => 'IP Mencurigakan',
                'group' => 'Monitoring',
                'parent' => 'dashboard.view',
                'description' => 'Menampilkan card dan daftar IP mencurigakan pada dashboard.',
            ],
            'report.view' => [
                'label' => 'Laporan',
                'group' => 'Laporan',
                'description' => 'Melihat dan mengekspor laporan deteksi malware.',
            ],
            self::PERMISSION_MANAGE_USERS => [
                'label' => 'Manajemen User',
                'group' => 'Administrasi',
                'description' => 'Membuat, mengubah, menghapus user, dan role.',
            ],
            self::PERMISSION_MANAGE_PERMISSIONS => [
                'label' => 'Hak Akses Menu',
                'group' => 'Administrasi',
                'description' => 'Mengatur hak akses dan izin menu untuk setiap user.',
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
    public static function dashboardCardPermissions(): array
    {
        return [
            self::PERMISSION_VIEW_DASHBOARD_DETECTION_CARD,
            self::PERMISSION_VIEW_DASHBOARD_SUSPICIOUS_IP_CARD,
        ];
    }

    /**
     * @param  list<string>  $permissions
     * @return list<string>
     */
    public static function normalizeSelectedPermissions(array $permissions): array
    {
        $permissionMetadata = self::permissions();

        return collect($permissions)
            ->intersect(self::permissionNames())
            ->reject(function (string $permission) use ($permissions, $permissionMetadata) {
                $parentPermission = $permissionMetadata[$permission]['parent'] ?? null;

                return $parentPermission !== null && ! in_array($parentPermission, $permissions, true);
            })
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function defaultUserPermissions(): array
    {
        return array_values(array_diff(self::permissionNames(), [self::PERMISSION_MANAGE_USERS, self::PERMISSION_MANAGE_PERMISSIONS]));
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

        $newPermissions = [];

        foreach (self::permissionNames() as $permission) {
            if (! Permission::query()->where('guard_name', 'web')->where('name', $permission)->exists()) {
                $newPermissions[] = $permission;
            }

            Permission::findOrCreate($permission, 'web');
        }

        $newDashboardCardPermissions = array_values(array_intersect($newPermissions, self::dashboardCardPermissions()));

        if ($newDashboardCardPermissions !== []) {
            User::permission('dashboard.view')->each(
                fn (User $user) => $user->givePermissionTo($newDashboardCardPermissions)
            );
        }

        Role::findOrCreate(self::ROLE_USER, 'web')->syncPermissions([]);
        Role::findOrCreate(self::ROLE_ADMIN, 'web')->syncPermissions([self::PERMISSION_MANAGE_USERS, self::PERMISSION_MANAGE_PERMISSIONS]);

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
            self::PERMISSION_MANAGE_USERS => 'admin.users.index',
        ];

        foreach ($routesByPermission as $permission => $routeName) {
            if ($user->can($permission)) {
                return $routeName;
            }
        }

        return 'profile.show';
    }
}
