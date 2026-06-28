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
    $dashboardDetectionPermission = \App\Support\AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION;
    $dashboardRawPermission = \App\Support\AccessControl::PERMISSION_VIEW_DASHBOARD_RAW;
    $dashboardDetectionDetailPermissions = \App\Support\AccessControl::dashboardDetectionDetailPermissions();
@endphp

<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.permissions.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Hak Akses Menu</a>
        <span class="text-gray-400 dark:text-gray-500">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Edit Hak Akses Menu</span>
    </x-slot>

    <!-- card info & edit form -->
<div class="w-full max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 space-y-6">
    <!-- User Details Information (Read Only) -->
    <div class="bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <p class="text-xs text-gray-400 dark:text-gray-300 font-semibold uppercase">Nama User</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ $user->name }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 dark:text-gray-300 font-semibold uppercase">Email</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mt-1">{{ $user->email }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 dark:text-gray-300 font-semibold uppercase">Role</p>
            <p class="mt-1">
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $selectedRole === 'admin' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ ucfirst($selectedRole) }}
                </span>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.permissions.update', $user) }}" class="space-y-6"
        x-data="{
            dashboardDetection: {{ in_array($dashboardDetectionPermission, $selectedPermissions, true) ? 'true' : 'false' }},
            dashboardRaw: {{ in_array($dashboardRawPermission, $selectedPermissions, true) ? 'true' : 'false' }}
        }">
        @csrf
        @method('PUT')

        <div>
            <div class="flex items-center justify-between gap-4 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Hak Akses Fitur</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Role admin selalu punya Manajemen User secara bawaan; akses fitur lain bisa diatur.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                @foreach ($groupedPermissions as $group => $items)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 h-fit min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ $group }}</p>

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
                                            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                                {{ $meta['label'] }}
                                            </span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                {{ $meta['description'] }}
                                            </span>
                                        </span>
                                    </label>

                                    @if ($hasChildren)
                                        <div class="ml-7 mt-3 space-y-3 border-l border-gray-200 dark:border-gray-600 pl-4" x-show="enabled" x-cloak>
                                            @foreach ($children as $childPermission => $childMeta)
                                                <label class="flex items-start gap-3">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $childPermission }}"
                                                        @checked(in_array($childPermission, $selectedPermissions, true))
                                                        @if ($childPermission === $dashboardDetectionPermission)
                                                            x-model="dashboardDetection"
                                                            @change="if (dashboardDetection) dashboardRaw = false"
                                                            :disabled="!enabled || dashboardRaw"
                                                        @elseif ($childPermission === $dashboardRawPermission)
                                                            x-model="dashboardRaw"
                                                            @change="if (dashboardRaw) {
                                                                dashboardDetection = false;
                                                                $root.querySelectorAll('[data-dashboard-detection-child]').forEach((input) => input.checked = false);
                                                            }"
                                                            :disabled="!enabled || dashboardDetection"
                                                        @elseif (in_array($childPermission, $dashboardDetectionDetailPermissions, true))
                                                            data-dashboard-detection-child
                                                            :disabled="!enabled || !dashboardDetection || dashboardRaw"
                                                        @else
                                                            :disabled="!enabled"
                                                        @endif
                                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    >
                                                    <span class="min-w-0">
                                                    <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                                        {{ $childMeta['label'] }}
                                                    </span>
                                                    <span class="block text-xs text-gray-500 dark:text-gray-400">
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

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end border-t border-gray-100 dark:border-gray-600 pt-4">
            <a href="{{ route('admin.permissions.index') }}"
                class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-semibold text-center">
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
