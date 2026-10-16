<x-app-layout>
    <x-slot name="title">Dashboard Guru</x-slot>

    <div class="py-6 lg:py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Banner sambutan + aksi utama guru --}}
            <section class="welcome-banner">
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">Dashboard Guru</p>
                        <h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Selamat datang, {{ $user->name }} 👋</h1>
                        <p class="mt-2 max-w-xl text-white/85">
                            Pantau perkembangan siswa, lihat gesture yang sering salah, dan bagikan tugas latihan baru.
                        </p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('teacher.assignments.create') }}"
                           class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 font-bold text-primary-dark shadow-sm transition hover:-translate-y-px">
                            <x-icon name="plus" class="h-5 w-5" /> Buat Tugas
                        </a>
                        <a href="{{ route('teacher.students.index') }}"
                           class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-white/15 px-5 py-2.5 font-semibold text-white ring-1 ring-white/30 transition hover:bg-white/25">
                            <x-icon name="users" class="h-5 w-5" /> Monitoring Siswa
                        </a>
                    </div>
                </div>
            </section>

            {{-- Statistik kelas --}}
            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4" aria-label="Statistik kelas">
                @foreach ([
                    ['users', 'bg-primary-50 text-primary', $overview['students'], 'Jumlah siswa'],
                    ['hand', 'bg-secondary-50 text-secondary-dark', $overview['weekly_sessions'], 'Latihan minggu ini'],
                    ['star', 'bg-amber-50 text-amber-500', $overview['avg_class_score'], 'Rata-rata skor kelas'],
                    ['clipboard', 'bg-rose-50 text-rose-500', $overview['active_assignments'], 'Assignment aktif'],
                ] as [$icon, $tone, $value, $label])
                    <div class="stat-card">
                        <span class="icon-tile {{ $tone }}"><x-icon :name="$icon" /></span>
                        <div class="stat-value">{{ $value }}</div>
                        <div class="text-sm text-ink-muted">{{ $label }}</div>
                    </div>
                @endforeach
            </section>

            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Monitoring siswa singkat --}}
                <section class="card">
                    <div class="flex items-center justify-between">
                        <h2 class="section-mini-title">👥 Aktivitas Siswa</h2>
                        <a href="{{ route('teacher.students.index') }}" class="btn-ghost">Lihat semua</a>
                    </div>
                    <ul class="mt-4 divide-y divide-surface-border">
                        @forelse ($students as $row)
                            <li>
                                <a href="{{ route('teacher.students.show', $row['student']) }}"
                                   class="-mx-2 flex items-center gap-3 rounded-xl px-2 py-3 transition hover:bg-surface-muted">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm font-extrabold text-primary-dark">
                                        {{ mb_strtoupper(mb_substr($row['name'], 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-bold text-ink">{{ $row['name'] }}</div>
                                        <div class="text-xs text-ink-muted">
                                            {{ $row['sessions'] }} sesi · {{ $row['last_activity']?->diffForHumans() ?? 'belum ada aktivitas' }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $row['avg_score'] >= 60 ? 'badge-teal' : ($row['sessions'] > 0 ? 'badge-amber' : 'badge-gray') }}">
                                        {{ $row['sessions'] > 0 ? $row['avg_score'] : '—' }}
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="py-6 text-center text-sm text-ink-muted">Belum ada siswa terdaftar.</li>
                        @endforelse
                    </ul>
                </section>

                {{-- Analisis error gesture --}}
                <section class="card">
                    <h2 class="section-mini-title">⚠️ Gesture Sering Salah</h2>
                    <p class="mt-1 text-sm text-ink-muted">Dari hasil recognition kelas — gunakan untuk menentukan materi ulang.</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($errorAnalysis as $row)
                            <div class="flex items-center gap-3">
                                <span class="badge badge-amber w-16 justify-center">{{ $row['gesture'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="truncate font-semibold text-ink">{{ $row['lesson'] }}</span>
                                        <span class="shrink-0 text-xs text-ink-muted">salah {{ $row['wrong'] }}/{{ $row['total'] }}</span>
                                    </div>
                                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-red-50" title="Akurasi {{ $row['accuracy'] }}%">
                                        <div class="h-full rounded-full {{ $row['accuracy'] >= 60 ? 'bg-teal-500' : 'bg-amber-500' }}" style="width: {{ $row['accuracy'] }}%"></div>
                                    </div>
                                </div>
                                <span class="w-12 shrink-0 text-right text-sm font-bold {{ $row['accuracy'] >= 60 ? 'text-teal-700' : 'text-amber-700' }}">{{ $row['accuracy'] }}%</span>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-ink-muted">Belum ada data error — siswa belum mulai berlatih.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Assignment aktif --}}
            <section class="card">
                <div class="flex items-center justify-between">
                    <h2 class="section-mini-title">📋 Assignment Aktif</h2>
                    <a href="{{ route('teacher.assignments.index') }}" class="btn-ghost">Kelola</a>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($activeAssignments as $assignment)
                        <a href="{{ route('teacher.assignments.show', $assignment) }}"
                           class="group rounded-2xl border border-surface-border p-4 transition hover:border-primary-100 hover:bg-primary-50/40">
                            <div class="font-bold text-ink group-hover:text-primary-dark">{{ $assignment->title }}</div>
                            <div class="mt-1 text-xs text-ink-muted">{{ $assignment->lesson?->title }}</div>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-ink-muted">
                                <x-icon name="calendar" class="h-4 w-4" />
                                {{ $assignment->due_at ? 'Deadline '.$assignment->due_at->format('d M Y') : 'Tanpa deadline' }}
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-surface-border p-6 text-center sm:col-span-2 lg:col-span-3">
                            <p class="font-semibold text-ink">Belum ada assignment aktif</p>
                            <p class="mt-1 text-sm text-ink-muted">Buat tugas pertama untuk dibagikan ke semua siswa.</p>
                            <a href="{{ route('teacher.assignments.create') }}" class="btn-primary mt-4">
                                <x-icon name="plus" class="h-5 w-5" /> Buat Tugas
                            </a>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
