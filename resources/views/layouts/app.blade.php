<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5b5ff0">

    <title>{{ $title ?? config('app.name', 'SignVerse') }} · {{ config('app.name', 'SignVerse') }}</title>

    <!-- Fonts — Outfit dipertahankan dari identitas visual SignVerse lama -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $currentUser = auth()->user();
    $isStudent = $currentUser->isStudent();

    /*
     * Menu per role (konsep st.navigation project lama). Satu sumber untuk
     * sidebar desktop, drawer mobile, dan bottom-nav mobile.
     * route null = menu belum tersedia (ditampilkan nonaktif "Segera").
     * short = label singkat di bottom-nav mobile (null = tidak tampil).
     */
    $menuGroups = $isStudent
        ? [
            'Belajar' => [
                ['label' => 'Dashboard', 'route' => 'student.dashboard', 'pattern' => 'student.dashboard', 'icon' => 'home', 'short' => 'Beranda'],
                ['label' => 'Materi Belajar', 'route' => 'student.materials.index', 'pattern' => 'student.materials.*', 'icon' => 'book', 'short' => 'Materi'],
                ['label' => 'AI Practice', 'route' => 'student.practice.index', 'pattern' => 'student.practice.*', 'icon' => 'hand', 'short' => 'Latihan'],
                ['label' => 'Challenge Harian', 'route' => 'student.challenge', 'pattern' => 'student.challenge', 'icon' => 'target', 'short' => 'Challenge'],
                ['label' => 'Assignment', 'route' => 'student.assignments.index', 'pattern' => 'student.assignments.*', 'icon' => 'clipboard', 'short' => 'Tugas'],
            ],
            'Perkembangan' => [
                ['label' => 'Progress', 'route' => 'student.progress', 'pattern' => 'student.progress', 'icon' => 'trending-up', 'short' => null],
                ['label' => 'Achievement', 'route' => 'student.achievements.index', 'pattern' => 'student.achievements.*', 'icon' => 'trophy', 'short' => null],
                ['label' => 'Inclusive Communication', 'route' => null, 'pattern' => null, 'icon' => 'message', 'short' => null],
            ],
        ]
        : [
            'Kelas' => [
                ['label' => 'Dashboard', 'route' => 'teacher.dashboard', 'pattern' => 'teacher.dashboard', 'icon' => 'home', 'short' => 'Beranda'],
                ['label' => 'Monitoring Siswa', 'route' => 'teacher.students.index', 'pattern' => 'teacher.students.*', 'icon' => 'users', 'short' => 'Siswa'],
                ['label' => 'Assignment', 'route' => 'teacher.assignments.index', 'pattern' => 'teacher.assignments.*', 'icon' => 'clipboard', 'short' => 'Tugas'],
                ['label' => 'Laporan & Report', 'route' => null, 'pattern' => null, 'icon' => 'chart', 'short' => null],
            ],
        ];

    $initials = collect(explode(' ', $currentUser->name))
        ->filter()
        ->take(2)
        ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp
<body class="font-sans antialiased" x-data="{ drawerOpen: false }" @keydown.escape.window="drawerOpen = false">

<a href="#main-content" class="sr-only z-[60] rounded-lg bg-white px-4 py-2 font-semibold text-primary focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
    Lewati ke konten utama
</a>

<div class="flex min-h-screen">

    {{-- Sidebar desktop --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-surface-border bg-white lg:flex">
        <div class="flex h-20 items-center gap-3 px-7">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-xl shadow-glow">🤟</span>
            <div>
                <div class="text-lg font-extrabold leading-tight tracking-tight text-ink">SignVerse</div>
                <div class="text-xs font-medium text-ink-muted">{{ $isStudent ? 'Ruang Belajar Siswa' : 'Panel Guru' }}</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 pb-4" aria-label="Menu utama">
            @include('layouts.partials.sidebar-nav', ['menuGroups' => $menuGroups])
        </nav>

        @include('layouts.partials.user-card', ['initials' => $initials, 'isStudent' => $isStudent])
    </aside>

    {{-- Topbar mobile --}}
    <div class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-surface-border bg-white/90 px-4 backdrop-blur lg:hidden">
        <a href="{{ $currentUser->dashboardRoute() }}" class="flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-lg">🤟</span>
            <span class="font-extrabold tracking-tight text-ink">SignVerse</span>
        </a>
        <button type="button" @click="drawerOpen = true"
                class="flex h-11 w-11 items-center justify-center rounded-xl text-ink hover:bg-surface"
                aria-label="Buka menu" :aria-expanded="drawerOpen.toString()">
            <x-icon name="menu" class="h-6 w-6" />
        </button>
    </div>

    {{-- Drawer mobile --}}
    <div class="lg:hidden" x-cloak>
        <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-ink/40 backdrop-blur-sm" @click="drawerOpen = false"></div>
        <aside x-show="drawerOpen"
               x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white shadow-lift"
               role="dialog" aria-modal="true" aria-label="Menu navigasi">
            <div class="flex h-16 items-center justify-between px-5">
                <span class="font-extrabold tracking-tight text-ink">Menu</span>
                <button type="button" @click="drawerOpen = false"
                        class="flex h-11 w-11 items-center justify-center rounded-xl text-ink-muted hover:bg-surface"
                        aria-label="Tutup menu">
                    <x-icon name="x" class="h-6 w-6" />
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 pb-4" aria-label="Menu utama">
                @include('layouts.partials.sidebar-nav', ['menuGroups' => $menuGroups])
            </nav>
            @include('layouts.partials.user-card', ['initials' => $initials, 'isStudent' => $isStudent])
        </aside>
    </div>

    {{-- Konten --}}
    <div class="flex min-h-screen w-full flex-1 flex-col pt-16 pb-20 lg:pt-0 lg:pb-0 lg:pl-72">
        @isset($header)
            <header class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 lg:px-8 lg:pt-10">
                {{ $header }}
            </header>
        @endisset

        {{-- Flash messages --}}
        @foreach (['error' => ['alert-error', 'alert'], 'status' => ['alert-success', 'check-circle']] as $flashKey => [$alertClass, $alertIcon])
            @if (session($flashKey))
                <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="alert {{ $alertClass }}" role="{{ $flashKey === 'error' ? 'alert' : 'status' }}">
                        <x-icon :name="$alertIcon" class="mt-0.5 h-5 w-5 shrink-0" />
                        <span class="flex-1">{{ session($flashKey) }}</span>
                        <button type="button" @click="show = false" class="-m-1 rounded-lg p-1 opacity-60 hover:opacity-100" aria-label="Tutup pesan">
                            <x-icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            @endif
        @endforeach

        <main id="main-content" class="flex-1 px-4 sm:px-0">
            {{ $slot }}
        </main>
    </div>

    {{-- Bottom navigation mobile — akses cepat ke menu yang paling sering dipakai --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-surface-border bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden"
         aria-label="Navigasi cepat">
        <div class="mx-auto flex max-w-lg items-stretch justify-around">
            @foreach (collect($menuGroups)->flatten(1)->filter(fn (array $item) => $item['short'] !== null) as $item)
                @php($active = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}"
                   class="flex min-h-16 flex-1 flex-col items-center justify-center gap-1 text-[11px] font-semibold transition {{ $active ? 'text-primary' : 'text-ink-muted hover:text-ink' }}"
                   @if ($active) aria-current="page" @endif>
                    <span class="flex h-8 w-12 items-center justify-center rounded-full transition {{ $active ? 'bg-primary-50' : '' }}">
                        <x-icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    {{ $item['short'] }}
                </a>
            @endforeach
        </div>
    </nav>
</div>
@stack('scripts')
</body>
</html>
