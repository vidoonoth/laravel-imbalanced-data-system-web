<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <a href="{{ Auth::user()->can('dashboard.view') ? route('dashboard') : route('profile.show') }}" class="hover:text-gray-900">Dashboard</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900 font-medium">Profile</span>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800">{{ __('Profile') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Informasi akun dan pengaturan profil Anda.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.edit') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    {{ __('Edit Profile') }}
                </a>
                <a href="{{ route('profile.password') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    {{ __('Ubah Kata Sandi') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        @if (session('status') === 'profile-updated')
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm font-medium text-green-800">
                Profil dan foto profil Anda berhasil diperbarui.
            </div>
        @endif
        {{-- Profile Card --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
            <div class="flex items-center gap-5 mb-8">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover flex-shrink-0 shadow-md">
                @else
                    <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                        <span class="text-white font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                    @if($user->roles->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Detail Akun') }}</h4>
                <dl class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 sm:w-40 flex-shrink-0">{{ __('Nama') }}</dt>
                        <dd class="text-sm text-gray-900 mt-1 sm:mt-0">{{ $user->name }}</dd>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 sm:w-40 flex-shrink-0">{{ __('Email') }}</dt>
                        <dd class="text-sm text-gray-900 mt-1 sm:mt-0 flex items-center gap-2">
                            {{ $user->email }}
                            @if($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    Belum Terverifikasi
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 sm:w-40 flex-shrink-0">{{ __('Bergabung') }}</dt>
                        <dd class="text-sm text-gray-900 mt-1 sm:mt-0">{{ $user->created_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</dd>
                    </div>
                    @if($user->permissions->isNotEmpty())
                        <div class="flex flex-col sm:flex-row sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 sm:w-40 flex-shrink-0">{{ __('Hak Akses') }}</dt>
                            <dd class="text-sm text-gray-900 mt-1 sm:mt-0">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($user->permissions as $permission)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('profile.edit') }}"
                class="group bg-white rounded-lg border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">{{ __('Edit Profile') }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Ubah nama dan alamat email Anda') }}</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('profile.password') }}"
                class="group bg-white rounded-lg border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">{{ __('Ubah Kata Sandi') }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Perbarui kata sandi untuk keamanan akun') }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-with-sidebar-layout>
