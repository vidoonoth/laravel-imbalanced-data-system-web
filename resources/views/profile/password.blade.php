<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">        
        <a href="{{ route('profile.show') }}" class="hover:text-gray-900">Profil</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 font-medium">Ubah Kata Sandi</span>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">{{ __('Ubah Kata Sandi') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.') }}</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('Kembali') }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        @if (session('status') === 'password-updated')
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm font-medium text-green-800">
                Kata sandi Anda berhasil diperbarui.
            </div>
        @endif
        {{-- Change Password Form --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-with-sidebar-layout>
