<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SignVerse') }} · {{ config('app.name', 'SignVerse') }}</title>

    <!-- Fonts — Outfit dipertahankan dari identitas visual SignVerse lama -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-ink">
<div class="flex min-h-screen">

    {{--
        Sidebar — menu berbeda per role (konsep dari st.navigation
        pada project lama). Student dan Teacher punya navigasi sendiri.
    --}}
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-surface-border bg-white lg:flex">
        <div class="flex h-16 items-center gap-2 border-b border-surface-border px-6">
            <span class="text-2xl">🤟</span>
            <span class="text-lg font-extrabold tracking-tight text-primary">SignVerse</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5">
            @if (auth()->user()->isStudent())
                {{-- Menu Student --}}
                <a href="{{ route('student.dashboard') }}"
                   class="nav-link {{ request()->routeIs('student.dashboard') ? 'nav-link-active' : '' }}">
                    <span>🏠</span> Dashboard
                </a>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 3)">
                    <span>📚</span> Materi Belajar
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 4)">
                    <span>✋</span> AI Practice
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 5)">
                    <span>🎯</span> Challenge Harian
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 3)">
                    <span>📈</span> Progress
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 5)">
                    <span>🏆</span> Achievement
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 6)">
                    <span>📝</span> Assignment
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 9)">
                    <span>💬</span> Inclusive Communication
                </span>
            @else
                {{-- Menu Teacher --}}
                <a href="{{ route('teacher.dashboard') }}"
                   class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'nav-link-active' : '' }}">
                    <span>🏠</span> Dashboard
                </a>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 7)">
                    <span>👥</span> Siswa
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 6)">
                    <span>📋</span> Assignment
                </span>
                <span class="nav-link cursor-not-allowed opacity-40" title="Segera (Phase 8)">
                    <span>📊</span> Laporan & Report
                </span>
            @endif
        </nav>

        <div class="border-t border-surface-border p-4">
            <div class="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">
                {{ auth()->user()->isStudent() ? 'Siswa' : 'Guru' }}
            </div>
            <div class="px-2 text-sm font-bold text-ink">{{ auth()->user()->name }}</div>
            <div class="mb-3 px-2 text-xs text-ink-muted">{{ auth()->user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary w-full !py-2 text-sm">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{--
        Mobile topbar + hamburger (Alpine.js) — sidebar jadi drawer
        di layar kecil.
    --}}
    <div class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-surface-border bg-white px-4 lg:hidden">
        <div class="flex items-center gap-2">
            <span class="text-xl">🤟</span>
            <span class="font-extrabold text-primary">SignVerse</span>
        </div>
        <button x-data @click="$dispatch('toggle-sidebar')" class="rounded-lg p-2 hover:bg-gray-100" aria-label="Buka menu">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    {{--
        Drawer overlay untuk mobile — ditangani Alpine.js store sederhana.
    --}}
    <div
        x-data="{ open: false }"
        @toggle-sidebar.window="open = ! open"
        class="lg:hidden"
    >
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/30" @click="open = false"></div>
        <aside x-show="open" x-transition class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white lg:hidden"
               x-trap.inert.noscroll="open">
            <div class="flex h-16 items-center gap-2 border-b border-surface-border px-6">
                <span class="text-2xl">🤟</span>
                <span class="text-lg font-extrabold text-primary">SignVerse</span>
            </div>
            <nav class="flex-1 space-y-1 px-4 py-5">
                @if (auth()->user()->isStudent())
                    <a href="{{ route('student.dashboard') }}" class="nav-link"><span>🏠</span> Dashboard</a>
                @else
                    <a href="{{ route('teacher.dashboard') }}" class="nav-link"><span>🏠</span> Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="px-3 pt-4">
                    @csrf
                    <button type="submit" class="btn-secondary w-full !py-2 text-sm">Keluar</button>
                </form>
            </nav>
        </aside>
    </div>

    {{-- Content --}}
    <div class="flex min-h-screen w-full flex-1 flex-col lg:pl-64">
        <header class="mt-16 border-b border-surface-border bg-white lg:mt-0">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                {{ $header ?? '' }}
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('error'))
            <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @if (session('status'))
            <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-700">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
