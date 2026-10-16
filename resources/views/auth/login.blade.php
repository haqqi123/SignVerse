<x-guest-layout>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink">Selamat datang kembali 👋</h1>
    <p class="mt-1 text-sm text-ink-muted">Masuk untuk melanjutkan belajar.</p>

    {{-- Session Status (mis. "Registrasi berhasil") --}}
    <x-auth-session-status class="mt-5" :status="session('status')" />

    {{-- Flash error dari middleware role (guest) --}}
    @if (session('error'))
        <div class="alert alert-error mt-5" role="alert">
            <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username"
                          placeholder="nama@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div x-data="{ showPassword: false }">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="mb-1.5 text-xs font-semibold text-primary hover:text-primary-dark" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <x-text-input id="password" class="pr-20" ::type="showPassword ? 'text' : 'password'" type="password" name="password"
                              required autocomplete="current-password" placeholder="••••••••" />
                <button type="button" @click="showPassword = ! showPassword"
                        class="absolute inset-y-0 right-0 px-3 text-xs font-bold text-ink-muted hover:text-primary"
                        x-text="showPassword ? 'Sembunyi' : 'Lihat'">Lihat</button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Remember Me --}}
        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-surface-border text-primary focus:ring-primary">
            <span class="text-sm text-ink-muted">Ingat saya</span>
        </label>

        <x-primary-button class="w-full">
            Masuk
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    {{-- Quick login demo — setara tombol "Masuk demo" pada project lama --}}
    <div class="mt-6">
        <div class="relative text-center">
            <div class="absolute inset-x-0 top-1/2 border-t border-surface-border"></div>
            <span class="relative bg-white px-3 text-xs font-semibold uppercase tracking-wider text-ink-soft">atau coba akun demo</span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="email" value="siswa@signteach.id">
                <input type="hidden" name="password" value="siswa123">
                <button type="submit" class="btn-secondary w-full text-sm">🎓 Demo Siswa</button>
            </form>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="email" value="guru@signteach.id">
                <input type="hidden" name="password" value="guru123">
                <button type="submit" class="btn-secondary w-full text-sm">👨‍🏫 Demo Guru</button>
            </form>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-ink-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-primary hover:text-primary-dark">Daftar gratis</a>
    </p>
</x-guest-layout>
