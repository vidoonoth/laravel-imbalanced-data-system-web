<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        AccessControl::ensureRolesAndPermissions();

        $admin = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('admin'),
        ]);

        AccessControl::assignAdminAccess($admin);

        User::query()
            ->whereKeyNot($admin->id)
            ->whereDoesntHave('roles')
            ->whereDoesntHave('permissions')
            ->each(fn (User $user) => AccessControl::assignDefaultUserAccess($user));
    }
}
