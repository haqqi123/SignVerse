<x-guest-layout>
    <h1 class="text-2xl font-extrabold tracking-tight text-ink">Buat akun siswa</h1>
    <p class="mt-1 text-sm text-ink-muted">Gratis — mulai belajar Bahasa Isyarat Indonesia hari ini.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        {{-- Nama --}}
        <div>
            <x-input-label for="name" :value="__('Nama lengkap')" />
            <x-text-input id="name" type="text" name="name"
                          :value="old('name')" required autofocus autocomplete="name"
                          placeholder="Nama kamu" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email"
                          :value="old('email')" required autocomplete="username"
                          placeholder="nama@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                          required autocomplete="new-password" placeholder="Ulangi password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Daftar Sekarang
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>

        <p class="text-center text-xs text-ink-muted">
            Setelah mendaftar, kamu akan diarahkan ke halaman masuk.
        </p>
    </form>

    <p class="mt-6 border-t border-surface-border pt-6 text-center text-sm text-ink-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-primary hover:text-primary-dark">Masuk di sini</a>
    </p>
</x-guest-layout>
