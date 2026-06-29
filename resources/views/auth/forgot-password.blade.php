<x-guest-layout>
    <main
        class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,#f8fafc_0%,#eefbff_42%,#f7fff5_100%)] dark:bg-[linear-gradient(135deg,#0f172a_0%,#1e293b_42%,#334155_100%)] px-4 py-8 text-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div aria-hidden="true"
            class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.05)_1px,transparent_1px)] dark:bg-[linear-gradient(rgba(148,163,184,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.05)_1px,transparent_1px)] bg-[size:46px_46px] opacity-40 [mask-image:linear-gradient(to_bottom,black,transparent_78%)]">
        </div>
        <div aria-hidden="true"
            class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white/80 dark:from-slate-900/80 to-transparent"></div>

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
                            <h2 class="text-2xl font-semibold leading-tight text-slate-950 dark:text-slate-100">Lupa Password</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Tulis email akun Anda untuk menerima tautan reset password.
                            </p>
                        </div>
                    </div>

                    <x-auth-session-status
                        class="mb-4 rounded-lg border border-emerald-200/80 dark:border-emerald-800/80 bg-emerald-50/80 dark:bg-emerald-900/30 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300 backdrop-blur"
                        :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                            <input id="email"
                                class="mt-2 block w-full rounded-lg border border-white/70 dark:border-gray-600 bg-white/65 dark:bg-gray-700/65 px-4 py-3 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-400 dark:focus:border-blue-500 focus:bg-white/85 dark:focus:bg-gray-700/85 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50"
                                type="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username" placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600 dark:text-rose-400" />
                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-slate-950 dark:bg-slate-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-300/80 dark:shadow-slate-900/80 transition hover:bg-slate-800 dark:hover:bg-slate-600 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700">
                            Kirim Tautan Reset
                        </button>
                    </form>

                    <div class="mt-6 border-t border-white/70 dark:border-gray-600 pt-5 text-center text-sm text-slate-600 dark:text-slate-400">
                        Ingat password?
                        <a href="{{ route('login') }}"
                            class="font-semibold text-blue-700 dark:text-blue-400 transition hover:text-blue-900 dark:hover:text-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                            Masuk
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
