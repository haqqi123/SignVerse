<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5b5ff0">

    <title>SignVerse — Platform AI Belajar Bahasa Isyarat Indonesia</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    {{-- Navbar publik --}}
    <nav class="sticky top-0 z-30 border-b border-surface-border/70 bg-white/80 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('landing') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-lg">🤟</span>
                <span class="text-lg font-extrabold tracking-tight text-ink">SignVerse</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary text-sm">Ke Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost min-h-11 px-4">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm">Daftar Gratis</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero — konsep dari landing.py project lama --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 -top-40 mx-auto h-[28rem] max-w-4xl rounded-full bg-gradient-to-r from-primary/25 to-secondary/25 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 pt-16 pb-20 text-center sm:px-6 lg:px-8 lg:pt-24 lg:pb-28">
            <span class="inline-flex items-center gap-2 rounded-full border border-primary-100 bg-white/80 px-4 py-1.5 text-xs font-bold text-primary-dark shadow-sm">
                <x-icon name="sparkles" class="h-4 w-4" /> SIBI · BISINDO · AI-Powered
            </span>
            <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-extrabold leading-[1.1] tracking-tight text-ink sm:text-5xl lg:text-6xl">
                Belajar Bahasa Isyarat Indonesia dengan
                <span class="text-gradient">bantuan AI</span>
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-ink-muted">
                Latihan langsung dengan kamera, nilai otomatis, gamifikasi yang bikin semangat,
                dan monitoring lengkap untuk guru.
            </p>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary w-full px-7 sm:w-auto">
                        Lanjut Belajar <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary w-full px-7 sm:w-auto">
                        Mulai Belajar Gratis <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary w-full px-7 sm:w-auto">Saya sudah punya akun</a>
                @endauth
            </div>

            {{-- Statistik singkat --}}
            <dl class="mx-auto mt-14 grid max-w-3xl grid-cols-3 gap-3 sm:gap-6">
                @foreach ([['2', 'Bahasa isyarat'], ['A–Z', 'Deteksi alfabet'], ['100%', 'Gratis untuk siswa']] as [$value, $label])
                    <div class="flex flex-col-reverse rounded-2xl border border-surface-border bg-white/70 p-4 backdrop-blur">
                        <dt class="text-xs font-semibold text-ink-muted sm:text-sm">{{ $label }}</dt>
                        <dd class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Fitur utama — dipertahankan dari project lama --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-primary">Fitur Utama</p>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Semua yang kamu butuhkan untuk fasih berbahasa isyarat</h2>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['hand', 'bg-primary-50 text-primary', 'AI Practice', 'Latihan isyarat dengan kamera, deteksi real-time, skor dan umpan balik otomatis.'],
                    ['trophy', 'bg-amber-50 text-amber-600', 'Gamification', 'XP, level, badge, streak, dan challenge harian untuk menjaga semangat belajar.'],
                    ['users', 'bg-secondary-50 text-secondary-dark', 'Lengkap untuk Guru', 'Dashboard kelas, monitoring tiap siswa, tugas, dan analisis gesture yang sering salah.'],
                ] as [$icon, $tone, $title, $description])
                    <div class="card card-hover">
                        <span class="icon-tile h-12 w-12 {{ $tone }}"><x-icon :name="$icon" class="h-6 w-6" /></span>
                        <h3 class="mt-5 text-lg font-extrabold text-ink">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ $description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section class="py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-extrabold tracking-tight text-ink">Mulai dalam 3 langkah</h2>
            <ol class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['Daftar akun', 'Buat akun siswa gratis hanya dengan nama dan email.'],
                    ['Pilih materi', 'Pelajari alfabet, angka, dan kosakata SIBI & BISINDO.'],
                    ['Latihan dengan AI', 'Peragakan isyarat di depan kamera dan dapatkan skor.'],
                ] as $index => [$title, $description])
                    <li class="relative rounded-card border border-surface-border bg-white p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary text-sm font-extrabold text-white">{{ $index + 1 }}</span>
                        <h3 class="mt-4 font-extrabold text-ink">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>

            @guest
                <div class="welcome-banner mt-14 text-center">
                    <h3 class="relative text-2xl font-extrabold">Siap mulai belajar?</h3>
                    <p class="relative mt-2 text-white/85">Bergabung sekarang dan kumpulkan XP pertamamu hari ini.</p>
                    <a href="{{ route('register') }}" class="relative mt-6 inline-flex min-h-11 items-center gap-2 rounded-xl bg-white px-6 py-2.5 font-bold text-primary-dark transition hover:-translate-y-px">
                        Daftar Gratis <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            @endguest
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-surface-border bg-white py-8 text-center text-sm text-ink-muted">
        SignVerse © 2026 — Platform AI Pembelajaran Bahasa Isyarat Indonesia · SIBI · BISINDO
    </footer>
</body>
</html>
