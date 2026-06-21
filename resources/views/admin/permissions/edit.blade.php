@php
    $groupOrder = [
        'Monitoring' => 1,
        'Administrasi' => 2,
        'Laporan' => 3,
    ];
    $permissionCollection = collect($permissions);
    $groupedPermissions = $permissionCollection
        ->filter(fn ($meta) => ! isset($meta['parent']) || $meta['parent'] === '')
        ->groupBy('group', true)
        ->sortBy(fn ($items, $group) => $groupOrder[$group] ?? 99);
    $childPermissions = $permissionCollection
        ->filter(fn ($meta) => isset($meta['parent']) && $meta['parent'] !== '')
        ->groupBy(fn ($meta) => $meta['parent'], true);
@endphp

<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.permissions.index') }}" class="hover:text-gray-900">Hak Akses Menu</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 font-medium">Edit Hak Akses Menu</span>
    </x-slot>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Edit Hak Akses Menu</h2>
                <p class="text-sm text-gray-500 mt-1">Sesuaikan menu dan fitur yang bisa diakses oleh user ini.</p>
            </div>
            <a href="{{ route('admin.permissions.index') }}"
                class="inline-flex justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                Kembali
            </a>
        </div>
    </x-slot>

    <!-- card info & edit form -->
<div class="w-full max-w-7xl mx-auto bg-white rounded-lg border border-gray-200 p-4 sm:p-6 space-y-6">
    <!-- User Details Information (Read Only) -->
    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-xs text-gray-400 font-semibold uppercase">Nama User</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $user->name }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-semibold uppercase">Email</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ $user->email }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-semibold uppercase">Role</p>
            <p class="mt-1">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $selectedRole === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($selectedRole) }}
                </span>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.permissions.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <div class="flex items-center justify-between gap-4 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Hak Akses Fitur</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Role admin selalu punya Manajemen User secara bawaan; akses fitur lain bisa diatur.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                @foreach ($groupedPermissions as $group => $items)
                    <div class="border border-gray-200 rounded-lg p-4 h-fit min-w-0">
                        <p class="text-sm font-semibold text-gray-800 mb-3">{{ $group }}</p>

                        <div class="space-y-3">
                            @foreach ($items as $permission => $meta)
                                @php
                                    $children = $childPermissions->get($permission, collect());
                                    $hasChildren = $children->isNotEmpty();
                                    $isPermissionSelected = in_array($permission, $selectedPermissions, true);
                                @endphp

                                <div @if ($hasChildren) x-data="{ enabled: {{ $isPermissionSelected ? 'true' : 'false' }} }" @endif>
                                    <label class="flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission }}"
                                            @checked($isPermissionSelected)
                                            @if ($hasChildren) x-model="enabled" @endif
                                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="min-w-0">
                                            <span class="block text-sm font-medium text-gray-800">
                                                {{ $meta['label'] }}
                                            </span>
                                            <span class="block text-xs text-gray-500">
                                                {{ $meta['description'] }}
                                            </span>
                                        </span>
                                    </label>

                                    @if ($hasChildren)
                                        <div class="ml-7 mt-3 space-y-3 border-l border-gray-200 pl-4" x-show="enabled" x-cloak>
                                            @foreach ($children as $childPermission => $childMeta)
                                                <label class="flex items-start gap-3">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $childPermission }}"
                                                        @checked(in_array($childPermission, $selectedPermissions, true))
                                                        :disabled="!enabled"
                                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    >
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-medium text-gray-800">
                                                            {{ $childMeta['label'] }}
                                                        </span>
                                                        <span class="block text-xs text-gray-500">
                                                            {{ $childMeta['description'] }}
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
            <x-input-error :messages="$errors->get('permissions.*')" class="mt-2" />
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end border-t border-gray-100 pt-4">
            <a href="{{ route('admin.permissions.index') }}"
                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold text-center">
                Batal
            </a>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                Simpan Hak Akses
            </button>
        </div>
    </form>
</div>
</x-app-with-sidebar-layout>
