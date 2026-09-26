<x-guest-layout>
    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Flash error dari middleware role (guest) --}}
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-xl font-extrabold text-ink">Masuk ke SignVerse</h1>
    <p class="mt-1 mb-6 text-sm text-ink-muted">Gunakan akun kamu untuk melanjutkan belajar.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username"
                          placeholder="nama@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full"
                          type="password" name="password"
                          required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Remember Me --}}
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-surface-border text-primary shadow-sm focus:ring-primary"
                       name="remember">
                <span class="ms-2 text-sm text-ink-muted">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-ink-muted underline hover:text-ink focus:outline-none focus:ring-2 focus:ring-primary"
                   href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif

            <x-primary-button class="!bg-primary !hover:bg-primary-dark">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>

    {{-- Quick login demo — setara tombol "Masuk demo" pada project lama --}}
    <div class="mt-8 border-t border-surface-border pt-6">
        <div class="rounded-lg bg-indigo-50 px-4 py-3 text-xs text-primary-dark">
            <span class="font-bold">Akun demo</span><br>
            Siswa: siswa@signteach.id / siswa123 · Guru: guru@signteach.id / guru123
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="email" value="siswa@signteach.id">
                <input type="hidden" name="password" value="siswa123">
                <button type="submit" class="btn-primary w-full !py-2 text-sm">
                    ⚡ Masuk demo siswa
                </button>
            </form>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="email" value="guru@signteach.id">
                <input type="hidden" name="password" value="guru123">
                <button type="submit" class="btn-secondary w-full !py-2 text-sm">
                    ⚡ Masuk demo guru
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 border-t border-surface-border pt-6 text-center">
        <a href="{{ route('register') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">
            Belum punya akun? Daftar di sini 📝
        </a>
    </div>
</x-guest-layout>
