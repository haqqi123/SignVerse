<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SignVerse') }}</title>

    <!-- Fonts — Outfit dipertahankan dari identitas visual SignVerse lama -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-ink antialiased">
<div class="flex min-h-screen flex-col items-center justify-center bg-surface px-4 py-8">

    {{-- Logo — kembali ke landing saat diklik --}}
    <a href="{{ route('landing') }}" class="mb-6 flex items-center gap-2">
        <span class="text-3xl">🤟</span>
        <span class="text-2xl font-extrabold text-primary">SignVerse</span>
    </a>

    <div class="w-full max-w-md rounded-card border border-surface-border bg-white p-6 shadow-card sm:p-8">
        {{ $slot }}
    </div>

    <p class="mt-6 text-xs text-ink-muted">
        SignVerse © 2026 — Platform AI Pembelajaran Bahasa Isyarat Indonesia
    </p>
</div>
</body>
</html>
