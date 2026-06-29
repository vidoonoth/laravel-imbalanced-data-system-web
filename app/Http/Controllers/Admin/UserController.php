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
            ->with(['roles'])
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
            'selectedRole' => AccessControl::ROLE_USER,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccessControl::ensureRolesAndPermissions();

        try {
            $validated = $this->validatedUserData($request);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $defaultPermissions = $validated['role'] === AccessControl::ROLE_ADMIN
                ? AccessControl::defaultAdminPermissions()
                : AccessControl::defaultUserPermissions();

            $this->syncUserAccess($user, $validated['role'], $defaultPermissions);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil dibuat.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat user. Silakan coba lagi.');
        }
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        try {
            $user->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }

    /**
     * @return array{name: string, email: string, password: string, role: string}
     */
    private function validatedUserData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(AccessControl::roles())],
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncUserAccess(User $user, string $role, array $permissions): void
    {
        $allowedPermissions = AccessControl::normalizeSelectedPermissions($permissions);

        if ($role === AccessControl::ROLE_ADMIN) {
            $user->syncRoles([AccessControl::ROLE_ADMIN]);
            $user->syncPermissions(array_values(array_diff($allowedPermissions, [
                AccessControl::PERMISSION_MANAGE_USERS,
                AccessControl::PERMISSION_MANAGE_PERMISSIONS
            ])));

            return;
        }

        $user->syncRoles([AccessControl::ROLE_USER]);
        $user->syncPermissions($allowedPermissions);
    }


}
