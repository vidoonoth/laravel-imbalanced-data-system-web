<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">       
        <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-gray-900">Profil</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 text-[23px] font-semibold">Edit</span>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>
</x-app-with-sidebar-layout>
