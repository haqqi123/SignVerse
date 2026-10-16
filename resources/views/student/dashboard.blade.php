<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @php
        $nextLesson = $recommended->first();
        $lessonPercent = $summary['lessons_total'] > 0
            ? (int) round($summary['lessons_mastered'] / $summary['lessons_total'] * 100)
            : 0;
        $challengesDone = $challenges->where('completed', 1)->count();
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    @endphp

    <div class="py-6 lg:py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Banner sambutan + aksi utama --}}
            <section class="welcome-banner">
                <div class="relative grid items-center gap-6 lg:grid-cols-[1fr_auto]">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">Dashboard Siswa</p>
                        <p class="mt-3 text-sm font-semibold text-white/80">{{ $greeting }},</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $user->name }} 👋</h1>
                        <p class="mt-2 max-w-xl text-white/85">
                            @if ($summary['streak'] > 0)
                                Kamu sudah belajar <strong>{{ $summary['streak'] }} hari berturut-turut</strong>. Pertahankan streak-mu hari ini!
                            @else
                                Yuk mulai latihan hari ini dan bangun streak belajarmu.
                            @endif
                        </p>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            @if ($nextLesson)
                                <a href="{{ route('student.practice.start', $nextLesson) }}"
                                   class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 font-bold text-primary-dark shadow-sm transition hover:-translate-y-px">
                                    <x-icon name="hand" class="h-5 w-5" />
                                    Lanjut latihan: {{ $nextLesson->title }}
                                </a>
                            @endif
                            <a href="{{ route('student.materials.index') }}"
                               class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-white/15 px-5 py-2.5 font-semibold text-white ring-1 ring-white/30 transition hover:bg-white/25">
                                <x-icon name="book" class="h-5 w-5" />
                                Lihat materi
                            </a>
                        </div>
                    </div>

                    {{-- Level & XP --}}
                    <div class="rounded-2xl bg-white/15 p-5 ring-1 ring-white/25 backdrop-blur lg:w-72">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-white/80">Level</span>
                            <span class="font-bold">{{ number_format($summary['xp']) }} XP</span>
                        </div>
                        <div class="mt-1 text-2xl font-extrabold">{{ $summary['level']['name'] }}</div>
                        <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-white/25"
                             role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $summary['level']['pct'] }}">
                            <div class="h-full rounded-full bg-white" style="width: {{ max(0, min(100, $summary['level']['pct'])) }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-white/80">
                            @if ($summary['level']['next_xp'] !== null)
                                {{ number_format($summary['level']['next_xp'] - $summary['xp']) }} XP lagi menuju level berikutnya
                            @else
                                Level tertinggi tercapai! 🎉
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- Statistik ringkas --}}
            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4" aria-label="Statistik belajar">
                <div class="stat-card">
                    <span class="icon-tile bg-orange-50 text-orange-500"><x-icon name="flame" /></span>
                    <div class="stat-value">{{ $summary['streak'] }} <span class="text-base font-bold text-ink-muted">hari</span></div>
                    <div class="text-sm text-ink-muted">Streak · rekor {{ $summary['longest_streak'] }} hari</div>
                </div>

                <div class="stat-card">
                    <span class="icon-tile bg-primary-50 text-primary"><x-icon name="target" /></span>
                    <div class="stat-value">{{ $challengesDone }}<span class="text-base font-bold text-ink-muted">/{{ $challenges->count() }}</span></div>
                    <div class="text-sm text-ink-muted">Challenge hari ini</div>
                </div>

                <div class="stat-card">
                    <span class="icon-tile bg-secondary-50 text-secondary-dark"><x-icon name="check-circle" /></span>
                    <div class="stat-value">{{ $summary['lessons_mastered'] }}<span class="text-base font-bold text-ink-muted">/{{ $summary['lessons_total'] }}</span></div>
                    <div class="text-sm text-ink-muted">Lesson dikuasai</div>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-secondary-50">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $lessonPercent }}%"></div>
                    </div>
                </div>

                @if ($newestBadge)
                    <a href="{{ route('student.achievements.index') }}" class="stat-card card-hover block">
                        <span class="icon-tile bg-amber-50 text-amber-500"><x-icon name="trophy" /></span>
                        <div class="mt-2 truncate text-lg font-extrabold text-ink">{{ $newestBadge['name'] }}</div>
                        <div class="text-sm text-ink-muted">Badge terbaru →</div>
                    </a>
                @else
                    <div class="stat-card">
                        <span class="icon-tile bg-amber-50 text-amber-500"><x-icon name="star" /></span>
                        <div class="stat-value">{{ $summary['sessions'] }}</div>
                        <div class="text-sm text-ink-muted">Sesi · rata-rata {{ $summary['avg_score'] }}</div>
                    </div>
                @endif
            </section>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Challenge harian --}}
                <section class="card">
                    <div class="flex items-center justify-between">
                        <h2 class="section-mini-title">🎯 Challenge Hari Ini</h2>
                        <a href="{{ route('student.challenge') }}" class="btn-ghost">Buka</a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @forelse ($challenges as $ch)
                            @php($challengePercent = $ch->target > 0 ? min(100, (int) round($ch->progress / $ch->target * 100)) : 0)
                            <div>
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="truncate font-semibold {{ $ch->completed ? 'text-teal-700' : 'text-ink' }}">
                                        {{ $ch->completed ? '✅' : '' }} {{ $ch->title }}
                                    </span>
                                    <span class="shrink-0 text-xs font-bold text-ink-muted">{{ min((int) $ch->progress, $ch->target) }}/{{ $ch->target }}</span>
                                </div>
                                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-primary-50">
                                    <div class="h-full rounded-full {{ $ch->completed ? 'bg-teal-500' : 'bg-primary' }}" style="width: {{ $challengePercent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada challenge hari ini.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Progress per kategori --}}
                <section class="card lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="section-mini-title">📈 Progress Belajar</h2>
                        <a href="{{ route('student.progress') }}" class="btn-ghost">Detail</a>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @forelse ($progress as $row)
                            <div class="rounded-2xl border border-surface-border p-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-ink">{{ $row['category']->icon }} {{ $row['category']->name }}</span>
                                    <span class="text-xs font-bold text-primary">{{ $row['percent'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-primary-50">
                                    <div class="xp-fill" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                                <div class="mt-1.5 text-xs text-ink-muted">{{ $row['done'] }} dari {{ $row['total'] }} lesson</div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada kategori pembelajaran.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Rekomendasi lesson --}}
                <section class="card">
                    <h2 class="section-mini-title">✨ Rekomendasi Latihan</h2>
                    <p class="mt-1 text-sm text-ink-muted">Lesson yang sebaiknya kamu latih berikutnya.</p>

                    <div class="mt-4 space-y-2">
                        @forelse ($recommended as $lesson)
                            <a href="{{ route('student.practice.start', $lesson) }}"
                               class="group flex items-center gap-3 rounded-xl border border-surface-border p-3 transition hover:border-primary-100 hover:bg-primary-50/50">
                                <span class="icon-tile bg-primary-50 text-primary group-hover:bg-white"><x-icon name="hand" class="h-5 w-5" /></span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-bold text-ink">{{ $lesson->title }}</div>
                                    <div class="truncate text-xs text-ink-muted">{{ $lesson->material->title }}</div>
                                </div>
                                <span class="badge badge-indigo shrink-0">+{{ $lesson->xp_reward }} XP</span>
                            </a>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada lesson. Tambahkan konten via seeder.</p>
                        @endforelse
                    </div>

                    <a href="{{ route('student.practice.index') }}" class="btn-primary mt-4 w-full">
                        <x-icon name="camera" class="h-5 w-5" /> Buka AI Practice
                    </a>
                </section>

                {{-- Aktivitas terakhir --}}
                <section class="card lg:col-span-2">
                    <h2 class="section-mini-title">🕒 Aktivitas Terakhir</h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                    <th class="py-2 pr-4">Lesson</th>
                                    <th class="hidden py-2 pr-4 sm:table-cell">Kategori</th>
                                    <th class="py-2 pr-4">Skor</th>
                                    <th class="py-2 pr-4">XP</th>
                                    <th class="hidden py-2 sm:table-cell">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSessions as $session)
                                    <tr class="border-b border-surface-border/60">
                                        <td class="py-3 pr-4 font-semibold text-ink">{{ $session->lesson->title }}</td>
                                        <td class="hidden py-3 pr-4 text-ink-muted sm:table-cell">{{ $session->lesson->material->category->name }}</td>
                                        <td class="py-3 pr-4">
                                            <span class="badge {{ $session->score >= 60 ? 'badge-teal' : 'badge-amber' }}">{{ $session->score }}</span>
                                        </td>
                                        <td class="py-3 pr-4 font-semibold text-primary">+{{ $session->xp_earned }}</td>
                                        <td class="hidden py-3 text-ink-muted sm:table-cell">{{ $session->completed_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center">
                                            <div class="text-3xl">✋</div>
                                            <p class="mt-2 font-semibold text-ink">Belum ada latihan</p>
                                            <p class="text-sm text-ink-muted">Mulai dari rekomendasi di samping, yuk!</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
