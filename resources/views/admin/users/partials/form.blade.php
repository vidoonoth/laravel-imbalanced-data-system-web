@php
    $isEditing = filled($user);
    $isChangingPassword = $isEditing && (old('change_password') || $errors->has('password'));
    $selectedRole = old('role', $selectedRole ?? 'user');
@endphp

<form method="POST" action="{{ $action }}" class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        @if ($isEditing)
            <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <label class="flex items-start gap-3">
                    <input id="changePasswordToggle" name="change_password" type="checkbox" value="1"
                        @checked($isChangingPassword)
                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-semibold text-gray-800">Ubah password</span>
                        <span class="block text-xs text-gray-500">Aktifkan hanya jika ingin mengganti password user ini.</span>
                    </span>
                </label>
            </div>
        @endif

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                {{ $isEditing ? 'Password Baru' : 'Password' }}
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password"
                @required(! $isEditing) @disabled($isEditing && ! $isChangingPassword)
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            @if ($isEditing)
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika password tidak diubah.</p>
            @endif
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                @required(! $isEditing) @disabled($isEditing && ! $isChangingPassword)
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
        <select id="role" name="role"
            class="mt-1 block w-full max-w-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.users.index') }}"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-semibold text-center">
            Batal
        </a>
        <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
            {{ $submitLabel }}
        </button>
    </div>
</form>

@if ($isEditing)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('changePasswordToggle');
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');

            if (!toggle || !password || !confirmation) {
                return;
            }

            function syncPasswordFields() {
                const enabled = toggle.checked;
                [password, confirmation].forEach(function(input) {
                    input.disabled = !enabled;
                    input.required = enabled;

                    if (!enabled) {
                        input.value = '';
                    }
                });
            }

            toggle.addEventListener('change', syncPasswordFields);
            syncPasswordFields();
        });
    </script>
@endif
