<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        AccessControl::ensureRolesAndPermissions();

        $users = User::query()
            ->with(['roles', 'permissions'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();

                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.permissions.index', [
            'users' => $users,
            'filters' => [
                'q' => $request->string('q')->toString(),
            ],
        ]);
    }

    public function edit(User $user): View
    {
        AccessControl::ensureRolesAndPermissions();

        $selectedRole = $user->roles->pluck('name')->first() ?: AccessControl::ROLE_USER;

        return view('admin.permissions.edit', [
            'user' => $user->load(['roles', 'permissions']),
            'permissions' => AccessControl::permissions(),
            'selectedRole' => $selectedRole,
            'selectedPermissions' => $user->getAllPermissions()->pluck('name')->unique()->values()->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        AccessControl::ensureRolesAndPermissions();

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(AccessControl::permissionNames())],
        ]);

        $role = $user->roles->pluck('name')->first() ?: AccessControl::ROLE_USER;
        $allowedPermissions = AccessControl::normalizeSelectedPermissions($validated['permissions'] ?? []);

        if ($role === AccessControl::ROLE_ADMIN) {
            $user->syncPermissions(array_values(array_diff($allowedPermissions, [
                AccessControl::PERMISSION_MANAGE_USERS,
                AccessControl::PERMISSION_MANAGE_PERMISSIONS
            ])));
        } else {
            $user->syncPermissions($allowedPermissions);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Hak akses user berhasil diperbarui.');
    }
}
