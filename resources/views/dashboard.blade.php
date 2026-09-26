<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Dashboard</h2>
        <p class="section-sub">{{ __('Selamat datang kembali di SignVerse.') }}</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="card">
                {{ __('Kamu sudah login. Konten dashboard penuh menyusul di phase berikutnya.') }}
            </div>
        </div>
    </div>
</x-app-layout>
