@props(['header' => null, 'breadcrumbs' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" x-init="init()" :class="{ 'dark': isDark }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        function themeManager() {
            return {
                isDark: false,
                init() {
                    this.isDark = localStorage.getItem('theme') === 'dark' || 
                        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }
    </script>
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    @php
        $homeUrl = route(\App\Support\AccessControl::homeRouteNameFor(Auth::user()));
        $dashboardDetectionPermission = \App\Support\AccessControl::PERMISSION_VIEW_DASHBOARD_DETECTION;
        $dashboardRawPermission = \App\Support\AccessControl::PERMISSION_VIEW_DASHBOARD_RAW;
    @endphp

    <div class="flex h-screen bg-gray-100 dark:bg-gray-900" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 text-black dark:text-gray-100 shadow-lg transition-all duration-300 overflow-visible"
            :class="{ 'hidden': !sidebarOpen }">
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="p-[22px] border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <a href="{{ $homeUrl }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <img src="{{ asset('images/logo-polindra.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="font-bold text-xl whitespace-nowrap">UPA TIK</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-2">
                    <!-- Dashboard Detection -->
                    @can($dashboardDetectionPermission)
                        <a href="{{ route('dashboard') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 9l7-4"></path>
                            </svg>
                            <span class="whitespace-nowrap">Dashboard Deteksi</span>
                        </a>
                    @endcan

                    <!-- Dashboard Raw Data -->
                    @can($dashboardRawPermission)
                        <a href="{{ route('dashboard.raw') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard.raw') ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7h16M4 12h16M4 17h16"></path>
                            </svg>
                            <span class="whitespace-nowrap">Dashboard Raw</span>
                        </a>
                    @endcan

                    <!-- Laporan -->
                    @can('report.view')
                        <a href="{{ route('report.index') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('report.*') ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="whitespace-nowrap">Laporan</span>
                        </a>
                    @endcan

                    <!-- User Management -->
                    @can('users.manage')
                        <a href="{{ route('admin.users.index') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            <span class="whitespace-nowrap">User</span>
                        </a>
                    @endcan

                    <!-- Hak Akses Menu -->
                    @can('permissions.manage')
                        <a href="{{ route('admin.permissions.index') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.permissions*') ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="whitespace-nowrap">Hak Akses Menu</span>
                        </a>
                    @endcan
                </nav>


            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <!-- Sidebar Toggle & Breadcrumb -->
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <svg class="w-6 h-6 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                            @if ($breadcrumbs)
                                {!! $breadcrumbs !!}
                            @else
                                <a href="{{ $homeUrl }}" class="hover:text-gray-900 dark:hover:text-gray-100">Home</a>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Theme Toggle -->
                        <x-theme-toggle />

                     <!-- User Profile Section -->
                        <div class="flex-shrink-0 bg-gray-200 dark:bg-gray-700 rounded-xl">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-3 p-3 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <div class="flex items-center space-x-3">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div
                                            class="w-10 h-10 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span
                                                class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="text-left">
                                        <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.show')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

                </div>
            </nav>

            <!-- Page Header (Optional) -->
            @if ($header)
                <header id="page-header" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 lg:px-8 py-6">
                    {{ $header }}
                </header>
            @endif

            <!-- Main Content Area -->
            <main id="main-content" class="flex-1 overflow-auto transition-opacity duration-200 bg-white dark:bg-gray-900">
                <div class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @if (session('success'))
        <x-notification type="success" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-notification type="error" message="{{ session('error') }}" />
    @endif

    @if (session('status') && !in_array(session('status'), ['profile-updated', 'password-updated']))
        <x-notification type="success" message="{{ session('status') }}" />
    @endif

    @if ($errors->any() && !$errors->has('password') && !$errors->has('current_password'))
        <x-notification type="error" message="{{ $errors->first() }}" />
    @endif

    @vite(['resources/js/navigation.js'])
</body>

</html>
