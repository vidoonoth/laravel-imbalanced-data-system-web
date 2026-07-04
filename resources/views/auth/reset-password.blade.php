<x-guest-layout>
    <main
        class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#f8fafc_0%,#eefbff_42%,#f7fff5_100%)] dark:bg-[linear-gradient(135deg,#0f172a_0%,#1e293b_42%,#334155_100%)] px-4 py-8 text-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(148,163,184,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.05)_1px,transparent_1px)] bg-[size:46px_46px] opacity-40 [mask-image:linear-gradient(to_bottom,black,transparent_78%)]">
        </div>
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/80 dark:from-slate-900/80 to-transparent"></div>

        <!-- Theme Toggle Button -->
        <div class="absolute top-4 right-4 z-50">
            <x-theme-toggle />
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-md items-center justify-center">
            <section class="w-full">
                <div
                    class="rounded-lg border border-white/70 dark:border-gray-700 bg-white/55 dark:bg-gray-800/55 p-5 shadow-[0_24px_80px_rgba(15,23,42,0.12)] dark:shadow-[0_24px_80px_rgba(0,0,0,0.3)] backdrop-blur-2xl sm:p-7">
                    <div class="mb-7">
                        <div class="flex items-center gap-3">
                            <a href="/"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-white/70 dark:border-gray-600 bg-white/70 dark:bg-gray-700/70 p-2 shadow-sm backdrop-blur-xl">
                                <img src="{{ asset('images/logo-polindra.png') }}" alt="Logo Polindra"
                                    class="h-full w-full object-contain">
                            </a>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-blue-700 dark:text-blue-400">Politeknik Negeri Indramayu</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Malware Detection System</p>
                            </div>
                        </div>
                        <div class="mt-7">
                            <h2 class="text-2xl font-semibold leading-tight text-slate-950 dark:text-slate-100">Reset Password</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Buat password baru untuk mengamankan akun Anda.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                            <input id="email"
                                class="mt-2 block w-full rounded-lg border border-white/70 dark:border-gray-600 bg-white/65 dark:bg-gray-700/65 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-400 dark:focus:border-blue-500 focus:bg-white/85 dark:focus:bg-gray-700/85 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                                type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                                autocomplete="username" placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600 dark:text-rose-400" />
                        </div>

                        <!-- Password -->
                        <div x-data="{ showPassword: false }">
                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password Baru</label>
                            <div class="relative mt-2">
                                <input id="password"
                                    class="block w-full rounded-lg border border-white/70 dark:border-gray-600 bg-white/65 dark:bg-gray-700/65 px-4 py-3 pr-11 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-400 dark:focus:border-blue-500 focus:bg-white/85 dark:focus:bg-gray-700/85 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                                    :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                    placeholder="Masukkan password baru">
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600 dark:text-rose-400" />
                        </div>

                        <!-- Confirm Password -->
                        <div x-data="{ showPasswordConfirmation: false }">
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi Password Baru</label>
                            <div class="relative mt-2">
                                <input id="password_confirmation"
                                    class="block w-full rounded-lg border border-white/70 dark:border-gray-600 bg-white/65 dark:bg-gray-700/65 px-4 py-3 pr-11 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-400 dark:focus:border-blue-500 focus:bg-white/85 dark:focus:bg-gray-700/85 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                                    :type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Konfirmasi password baru">
                                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 transition">
                                    <svg x-show="!showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-600 dark:text-rose-400" />
                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-slate-950 dark:bg-slate-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 dark:hover:bg-slate-600 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700">
                            Reset Password
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
