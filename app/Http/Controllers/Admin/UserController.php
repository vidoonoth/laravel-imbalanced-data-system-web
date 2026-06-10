<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
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

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'q' => $request->string('q')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        AccessControl::ensureRolesAndPermissions();

        return view('admin.users.create', [
            'roles' => AccessControl::roles(),
            'permissions' => AccessControl::permissions(),
            'selectedRole' => AccessControl::ROLE_USER,
            'selectedPermissions' => AccessControl::defaultUserPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccessControl::ensureRolesAndPermissions();

        $validated = $this->validatedUserData($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->syncUserAccess($user, $validated['role'], $validated['permissions'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        AccessControl::ensureRolesAndPermissions();

        return view('admin.users.edit', [
            'user' => $user->load(['roles', 'permissions']),
            'roles' => AccessControl::roles(),
            'permissions' => AccessControl::permissions(),
            'selectedRole' => $user->roles->pluck('name')->first() ?: AccessControl::ROLE_USER,
            'selectedPermissions' => $this->effectivePermissionNames($user),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        AccessControl::ensureRolesAndPermissions();

        $validated = $this->validatedUserData($request, $user);

        if ($user->is($request->user()) && $validated['role'] !== AccessControl::ROLE_ADMIN) {
            return back()
                ->withErrors(['role' => 'Role admin pada akun yang sedang digunakan tidak dapat dilepas.'])
                ->withInput();
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->syncUserAccess($user, $validated['role'], $validated['permissions'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil dihapus.');
    }

    /**
     * @return array{name: string, email: string, password?: string|null, role: string, permissions?: list<string>}
     */
    private function validatedUserData(Request $request, ?User $user = null): array
    {
        $passwordRules = ['required', 'confirmed', Rules\Password::defaults()];

        if ($user) {
            if (! $request->boolean('change_password')) {
                $request->merge([
                    'password' => null,
                    'password_confirmation' => null,
                ]);
            }

            $passwordRules = $request->boolean('change_password')
                ? ['required', 'confirmed', Rules\Password::defaults()]
                : ['nullable'];
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user),
            ],
            'password' => $passwordRules,
            'role' => ['required', Rule::in(AccessControl::roles())],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(AccessControl::permissionNames())],
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncUserAccess(User $user, string $role, array $permissions): void
    {
        $allowedPermissions = collect($permissions)
            ->intersect(AccessControl::permissionNames())
            ->values()
            ->all();

        if ($role === AccessControl::ROLE_ADMIN) {
            $user->syncRoles([AccessControl::ROLE_ADMIN]);
            $user->syncPermissions(array_values(array_diff($allowedPermissions, [AccessControl::PERMISSION_MANAGE_USERS])));

            return;
        }

        $user->syncRoles([AccessControl::ROLE_USER]);
        $user->syncPermissions($allowedPermissions);
    }

    /**
     * @return list<string>
     */
    private function effectivePermissionNames(User $user): array
    {
        return $user->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }
}
