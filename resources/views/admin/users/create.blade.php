<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Kelola Data User</a>
        <span class="text-gray-400 dark:text-gray-500">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Tambah User</span>
    </x-slot>

    @include('admin.users.partials.form', [
        'action' => route('admin.users.store'),
        'method' => 'POST',
        'user' => null,
        'submitLabel' => 'Simpan User',
    ])
</x-app-with-sidebar-layout>
