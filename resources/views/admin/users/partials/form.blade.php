@php
    $selectedRole = old('role', $selectedRole ?? 'user');
@endphp

<form method="POST" action="{{ $action }}" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                placeholder="Masukkan nama lengkap"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                placeholder="Masukkan alamat email"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="new-password" required
                    placeholder="Masukkan password"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10">
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
            <div class="relative">
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    placeholder="Konfirmasi password"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 pr-10">
                <button type="button"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
        <select id="role" name="role"
            class="mt-1 block w-full max-w-sm rounded-lg border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role === 'user' ? 'Petugas' : ucfirst($role) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.users.index') }}"
            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-semibold text-center">
            Batal
        </a>
        <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
            {{ $submitLabel }}
        </button>
    </div>
<script>
    function setupPasswordToggle(passwordInputId) {
        const passwordInput = document.getElementById(passwordInputId);
        const passwordToggle = passwordInput.nextElementSibling;

        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.querySelector('svg:nth-child(1)').classList.toggle('hidden', type === 'text');
            this.querySelector('svg:nth-child(2)').classList.toggle('hidden', type === 'password');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupPasswordToggle('password');
        setupPasswordToggle('password_confirmation');
    });
</script>
