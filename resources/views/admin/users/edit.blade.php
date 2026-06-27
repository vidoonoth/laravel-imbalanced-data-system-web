<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Kelola Data User</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 text-[23px] font-semibold">Edit User</span>
    </x-slot>

    @include('admin.users.partials.form', [
        'action' => route('admin.users.update', $user),
        'method' => 'PUT',
        'user' => $user,
        'submitLabel' => 'Perbarui User',
    ])
</x-app-with-sidebar-layout>
