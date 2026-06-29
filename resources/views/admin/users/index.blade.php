<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 dark:text-gray-100 hover:text-gray-900 text-[23px] font-semibold">Kelola Data User</span>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                        placeholder="Cari nama atau email"
                        class="w-full sm:max-w-sm rounded-lg border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-white dark:placeholder-white focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white dark:bg-gray-600 rounded-lg hover:bg-gray-900 hover:dark:bg-gray-700 transition text-sm font-semibold">
                        Cari
                    </button>
                    @if ($filters['q'] !== '')
                        <a href="{{ route('admin.users.index') }}"
                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-semibold text-center">
                            Reset
                        </a>
                    @endif
                </form>
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                    Tambah User
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">User</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Role</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($users as $user)
                        @php
                            $roleName = $user->roles->pluck('name')->first() ?? 'user';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleName === 'admin' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    {{ ucfirst($roleName) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    @if(!$user->is(auth()->user()))
                                        <x-delete-modal :action="route('admin.users.destroy', $user)" title="Hapus User" message="Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.">
                                            <x-slot name="trigger">
                                                Hapus
                                            </x-slot>
                                        </x-delete-modal>
                                    @else
                                        <button type="button" disabled
                                            class="px-3 py-2 bg-red-600 text-white rounded-lg transition text-xs font-semibold opacity-50 cursor-not-allowed">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada user ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
        </div>
    </div>
</x-app-with-sidebar-layout>
