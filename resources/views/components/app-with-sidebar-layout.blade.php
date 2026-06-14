@props(['header' => null, 'breadcrumbs' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
</head>

<body class="font-sans antialiased bg-gray-50">
    @php
        $homeUrl = Auth::user()->can('dashboard.view')
            ? route('dashboard')
            : (Auth::user()->can('detection.run')
                ? route('detection')
                : route('profile.show'));
    @endphp

    <div class="flex h-screen bg-gray-100" x-data="{ sidebarOpen: true }">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r text-black shadow-lg transition-all duration-300 overflow-visible"
            :class="{ 'hidden': !sidebarOpen }">
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="p-4 border-b flex items-center justify-between flex-shrink-0">
                    <a href="{{ $homeUrl }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <img src="{{ asset('images/logo-polindra.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="font-bold text-lg whitespace-nowrap">UPA TIK</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-2">
                    <!-- Dashboard Overview -->
                    @can('dashboard.view')
                        <a href="{{ route('dashboard') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-200' : 'hover:bg-gray-400' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 9l7-4"></path>
                            </svg>
                            <span class="whitespace-nowrap">Dashboard</span>
                        </a>
                    @endcan

                    <!-- Detection -->
                    @can('detection.run')
                        <a href="{{ route('detection') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('detection') ? 'bg-gray-200' : 'hover:bg-gray-400' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="whitespace-nowrap">Detection</span>
                        </a>
                    @endcan

                    <!-- Detection History -->
                    @can('detection-history.view')
                        <a href="{{ route('detection.history') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('detection.history*') ? 'bg-gray-200' : 'hover:bg-gray-400' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="whitespace-nowrap">Riwayat</span>
                        </a>
                    @endcan

                    <!-- User Management -->
                    @can('users.manage')
                        <a href="{{ route('admin.users.index') }}" data-nav-link
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-gray-200' : 'hover:bg-gray-400' }} transition cursor-pointer">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            <span class="whitespace-nowrap">User</span>
                        </a>
                    @endcan
                </nav>

               
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <!-- Sidebar Toggle & Breadcrumb -->
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-600">
                            @if ($breadcrumbs)
                                {!! $breadcrumbs !!}
                            @else
                                <a href="{{ $homeUrl }}" class="hover:text-gray-900">Home</a>
                            @endif
                        </div>
                    </div>

                     <!-- User Profile Section -->
                <div class="flex-shrink-0">
                    <x-dropdown align="side" width="48">
                        <x-slot name="trigger">
                            <button
                                class="w-full flex items-center justify-between rounded-lg hover:bg-gray-400 transition">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span
                                            class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
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
            </nav>

            <!-- Page Header (Optional) -->
            @if ($header)
                <header id="page-header" class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
                    {{ $header }}
                </header>
            @endif

            <!-- Main Content Area -->
            <main id="main-content" class="flex-1 overflow-auto transition-opacity duration-200 bg-white">
                <div class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @vite(['resources/js/navigation.js'])
</body>

</html>
