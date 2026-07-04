<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ route('profile.show') }}" class="text-gray-600 dark:text-gray-100 hover:text-gray-900 dark:hover:text-gray-100">Profil</a>
        <span class="text-gray-400 dark:text-gray-500">/</span>
        <span class="text-gray-900 dark:text-gray-100 text-[23px] font-semibold">Edit</span>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>
</x-app-with-sidebar-layout>
