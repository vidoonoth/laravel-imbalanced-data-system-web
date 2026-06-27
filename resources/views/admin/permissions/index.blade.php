@php
    $permissionDisplayLabels = [
        'dashboard.view' => 'dashboard',
        'dashboard.detection.view' => 'dashboard hasil deteksi',
        'dashboard.raw.view' => 'dashboard raw data',
        'report.view' => 'report',
        'users.manage' => 'kelola data user',
        'permissions.manage' => 'kelola hak akses menu',
        'dashboard.detection-card.view' => 'deteksi',
        'dashboard.suspicious-ip-card.view' => 'ip mencurigakan',
    ];
@endphp

<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 hover:text-gray-900 text-[23px] font-semibold">Hak Akses Menu</span>
    </x-slot>

    @if (session('status'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 text-sm font-medium text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <input type="text" name="q" value="{{ $filters['q'] }}"
                    placeholder="Cari nama atau email"
                    class="w-full sm:max-w-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <button type="submit"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition text-sm font-semibold">
                    Cari
                </button>
                @if ($filters['q'] !== '')
                    <a href="{{ route('admin.permissions.index') }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold text-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">User</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Role</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Hak Akses</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        @php
                            $roleName = $user->roles->pluck('name')->first() ?? 'user';
                            $permissionNames = $user->getAllPermissions()->pluck('name')->unique()->values();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleName === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($roleName) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2 max-w-2xl">
                                    @forelse ($permissionNames as $permission)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                                            {{ $permissionDisplayLabels[$permission] ?? $permission }}
                                        </span>
                                    @empty
                                        <span class="text-gray-500 text-xs">Belum ada akses</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.permissions.edit', $user) }}"
                                        class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs font-semibold">
                                        Edit Hak Akses
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                Tidak ada user ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
</x-app-with-sidebar-layout>
