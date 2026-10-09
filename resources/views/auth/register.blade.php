<x-guest-layout>
    <h1 class="text-xl font-extrabold text-ink">Daftar Akun Baru</h1>
    <p class="mt-1 mb-6 text-sm text-ink-muted">Mulai belajar Bahasa Isyarat Indonesia hari ini.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nama --}}
        <div>
            <x-input-label for="name" :value="__('Nama lengkap')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name"
                          :value="old('name')" required autofocus autocomplete="name"
                          placeholder="Nama Kamu" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                          :value="old('email')" required autocomplete="username"
                          placeholder="nama@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password"
                          required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Konfirmasi password --}}
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full"
                          type="password" name="password_confirmation"
                          required autocomplete="new-password" placeholder="Ulangi password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-end">
            <x-primary-button>
                {{ __('Daftar Sekarang') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-surface-border pt-6 text-center">
        <p class="mb-2 text-sm text-ink-muted">Sudah punya akun?</p>
        <a href="{{ route('login') }}" class="btn-secondary !py-2 text-sm">Masuk ke Akun</a>
    </div>
</x-guest-layout>
