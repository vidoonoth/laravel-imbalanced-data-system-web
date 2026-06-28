<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">        
        <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-gray-900">Profil</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 text-[23px] font-semibold">Ubah Kata Sandi</span>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        {{-- Change Password Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-with-sidebar-layout>
