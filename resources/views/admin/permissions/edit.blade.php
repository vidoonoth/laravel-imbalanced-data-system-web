@php
    $groupedPermissions = collect($permissions)->groupBy('group', true);
@endphp

<x-app-with-sidebar-layout>
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

    <div class="bg-white rounded-lg border border-gray-200 p-6 max-w-4xl space-y-6">
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
                        <p class="text-xs text-gray-500 mt-1">Role admin selalu punya Manajemen User secara bawaan; akses fitur lain bisa diatur.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($groupedPermissions as $group => $items)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-800 mb-3">{{ $group }}</p>
                            <div class="space-y-3">
                                @foreach ($items as $permission => $meta)
                                    <label class="flex items-start gap-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                            @checked(in_array($permission, $selectedPermissions, true))
                                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span>
                                            <span class="block text-sm font-medium text-gray-800">{{ $meta['label'] }}</span>
                                            <span class="block text-xs text-gray-500">{{ $meta['description'] }}</span>
                                        </span>
                                    </label>
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
