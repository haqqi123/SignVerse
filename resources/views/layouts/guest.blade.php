<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5b5ff0">

    <title>{{ $title ?? config('app.name', 'SignVerse') }}</title>

    <!-- Fonts — Outfit dipertahankan dari identitas visual SignVerse lama -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="flex min-h-screen">

    {{-- Panel brand (desktop) --}}
    <aside class="relative hidden w-[44%] overflow-hidden bg-gradient-to-br from-primary via-indigo-500 to-secondary p-12 text-white lg:flex lg:flex-col">
        <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-white/10"></div>

        <a href="{{ route('landing') }}" class="relative flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 text-2xl backdrop-blur">🤟</span>
            <span class="text-xl font-extrabold tracking-tight">SignVerse</span>
        </a>

        <div class="relative my-auto max-w-md">
            <h2 class="text-4xl font-extrabold leading-tight tracking-tight">
                Belajar bahasa isyarat jadi lebih seru & terarah.
            </h2>
            <p class="mt-4 text-lg text-white/80">
                Latihan dengan kamera, dapat skor otomatis, kumpulkan XP & badge — guru bisa memantau perkembangan kelas secara langsung.
            </p>

            <ul class="mt-8 space-y-3 text-sm font-semibold">
                @foreach (['✋ AI Practice dengan deteksi gesture', '🏆 XP, level, streak & badge', '👨‍🏫 Monitoring & tugas dari guru'] as $point)
                    <li class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 backdrop-blur">{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-white/70">SignVerse © 2026 · SIBI · BISINDO</p>
    </aside>

    {{-- Form --}}
    <main class="flex flex-1 flex-col items-center justify-center bg-surface px-4 py-10 sm:px-8">
        <a href="{{ route('landing') }}" class="mb-8 flex items-center gap-2 lg:hidden">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-xl">🤟</span>
            <span class="text-xl font-extrabold tracking-tight text-ink">SignVerse</span>
        </a>

        <div class="w-full max-w-md rounded-card border border-surface-border bg-white p-6 shadow-card sm:p-8">
            {{ $slot }}
        </div>

        <a href="{{ route('landing') }}" class="mt-6 text-sm font-semibold text-ink-muted hover:text-primary">
            ← Kembali ke beranda
        </a>
    </main>
</div>
</body>
</html>
