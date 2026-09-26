<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SignVerse — Platform AI Belajar Bahasa Isyarat Indonesia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-ink">

    {{-- Navbar publik --}}
    <nav class="border-b border-surface-border bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🤟</span>
                <span class="text-lg font-extrabold text-primary">SignVerse</span>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary !py-2 text-sm">
                        Ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-ink-muted hover:text-ink">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary !py-2 text-sm">Daftar</a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero — konsep dari landing.py project lama --}}
    <section class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8 lg:py-24">
        <p class="text-xs font-bold uppercase tracking-[0.35em] text-ink-muted">SignVerse · SIBI · BISINDO · AI-Powered</p>
        <h1 class="mt-4 text-5xl font-extrabold tracking-tight lg:text-6xl">
            <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">SignVerse</span>
        </h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-ink-muted">
            Platform AI untuk Belajar Bahasa Isyarat Indonesia — latihan dengan kamera,
            nilai otomatis, gamifikasi, dan monitoring lengkap untuk guru.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
            <span class="badge badge-indigo">SIBI</span>
            <span class="badge badge-teal">BISINDO</span>
            <span class="badge badge-amber">AI-Powered</span>
        </div>
        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            @auth
                <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary">Mulai Belajar</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary">Mulai Belajar</a>
                <a href="{{ route('login') }}" class="btn-secondary">Sudah punya akun</a>
            @endif
        </div>
    </section>

    {{-- Fitur utama — dipertahankan dari project lama --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <h2 class="section-title text-center">Fitur Utama</h2>
        <p class="section-sub mb-10 text-center">Semua yang kamu butuhkan untuk fasih berbahasa isyarat</p>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="card text-center">
                <div class="text-4xl">✋</div>
                <h3 class="mt-3 font-extrabold">AI Practice</h3>
                <p class="mt-2 text-sm text-ink-muted">
                    Latihan isyarat dengan kamera, deteksi real-time, skor dan umpan balik otomatis.
                </p>
            </div>
            <div class="card text-center">
                <div class="text-4xl">🏆</div>
                <h3 class="mt-3 font-extrabold">Gamification</h3>
                <p class="mt-2 text-sm text-ink-muted">
                    XP, level, badge, dan streak untuk menjaga semangat belajar.
                </p>
            </div>
            <div class="card text-center">
                <div class="text-4xl">👨‍🏫</div>
                <h3 class="mt-3 font-extrabold">Lengkap untuk Guru</h3>
                <p class="mt-2 text-sm text-ink-muted">
                    Dashboard, monitoring siswa, assignment, dan laporan analisis kelas.
                </p>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-surface-border bg-white py-8 text-center text-sm text-ink-muted">
        SignVerse © 2026 — Platform AI Pembelajaran Bahasa Isyarat Indonesia · SIBI · BISINDO
    </footer>
</body>
</html>
