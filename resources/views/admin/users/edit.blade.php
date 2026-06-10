<x-app-with-sidebar-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">Edit User</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $user->name }} - {{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold">
                Kembali
            </a>
        </div>
    </x-slot>

    @include('admin.users.partials.form', [
        'action' => route('admin.users.update', $user),
        'method' => 'PUT',
        'user' => $user,
        'submitLabel' => 'Perbarui User',
    ])
</x-app-with-sidebar-layout>
