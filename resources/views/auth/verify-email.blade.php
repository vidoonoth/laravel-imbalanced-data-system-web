<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" x-init="init()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verifikasi Email - {{ config('app.name', 'UPA TIK') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-polindra.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-polindra.png') }}?v=2">

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
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
        <!-- Logo & Theme Toggle -->
        <div class="w-full max-w-md flex justify-between items-center mb-6">
            <div class="flex-1"></div>
            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/logo-polindra.png') }}" alt="Logo Polindra" class="w-16 h-16">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">UPA TIK</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Politeknik Negeri Indramayu</p>
                </div>
            </div>
            <div class="flex-1 flex justify-end">
                <x-theme-toggle />
            </div>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-lg px-6 py-8">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-2">
                Verifikasi Email Anda
            </h2>

            <!-- Message -->
            <div class="mb-6 text-sm text-gray-600 dark:text-gray-400 text-center">
                <p>Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan kepada Anda?</p>
                <p class="mt-2">Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan yang baru.</p>
            </div>

            <!-- Success Message -->
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="space-y-3">
                <!-- Resend Button -->
                <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Kirim Ulang Email Verifikasi</span>
                    </button>
                </form>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-semibold">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} UPA TIK - Politeknik Negeri Indramayu</p>
        </div>
    </div>
</body>
</html>
