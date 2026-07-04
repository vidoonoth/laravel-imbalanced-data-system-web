@php
    $permissionDisplayLabels = [
        'dashboard.view' => 'dashboard',
        'dashboard.detection.view' => 'dashboard hasil deteksi',
        'dashboard.raw.view' => 'dashboard raw data',
        'report.view' => 'report',
        'users.manage' => 'kelola data user',
        'permissions.manage' => 'kelola hak akses menu',
        'dashboard.detection-card.view' => 'deteksi',
        'dashboard.suspicious-ip-card.view' => 'ip mencurigakan',
    ];
@endphp

<x-app-with-sidebar-layout>
    <x-slot name="breadcrumbs">
        <span class="text-gray-900 dark:text-gray-100 hover:text-gray-900 dark:hover:text-gray-100 text-[23px] font-semibold">Profil</span>
    </x-slot>

    <div class="max-w-6xl">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Profile Card --}}
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                <div class="flex items-center gap-5 mb-8">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover flex-shrink-0 shadow-md">
                    @else
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                            <span class="text-white font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-200 mt-0.5">{{ $user->email }}</p>
                        @if($user->roles->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role->name === 'admin' ? 'bg-blue-500 dark:bg-blue-600 text-gray-100 dark:text-gray-100' : 'bg-blue-500 dark:bg-blue-600 text-gray-100 dark:text-gray-100' }}">
                                        {{ $role->name === 'user' ? 'Petugas' : ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-200 rounded-xl tracking-wider mb-4">{{ __('Detail Akun') }}</h4>
                    <dl class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-40 flex-shrink-0">{{ __('Nama') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 sm:mt-0">{{ $user->name }}</dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-40 flex-shrink-0">{{ __('Email') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 sm:mt-0 flex items-center gap-2">
                                {{ $user->email }}
                            </dd>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-40 flex-shrink-0">{{ __('Bergabung') }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 sm:mt-0">{{ $user->created_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</dd>
                        </div>
                        @if($user->permissions->isNotEmpty())
                            <div class="flex flex-col sm:flex-row sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:w-40 flex-shrink-0">{{ __('Hak Akses') }}</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 sm:mt-0">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($user->permissions as $permission)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                {{ $permissionDisplayLabels[$permission->name] ?? $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Action Buttons (Side) --}}
            <div class="flex flex-col gap-4 lg:w-80">
                <a href="{{ route('profile.edit') }}"
                    class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-sm transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Profil') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Ubah nama dan alamat email Anda') }}</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('profile.password') }}"
                    class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-sm transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 dark:group-hover:bg-amber-900/50 transition">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Ubah Kata Sandi') }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Perbarui kata sandi untuk keamanan akun') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-with-sidebar-layout>
