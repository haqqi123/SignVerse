<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Dashboard Siswa</h2>
        <p class="section-sub">Selamat datang, {{ $user->name }} 👋</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Statistik utama (via StudentStatsService) --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">Level & XP</div>
                    <div class="stat-value">{{ $summary['level'] }}</div>
                    <div class="text-xs font-semibold text-ink-muted">{{ number_format($summary['xp']) }} XP total</div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-primary" style="width: {{ min(100, $summary['xp_in_level']) }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-ink-muted">{{ $summary['xp_in_level'] }}/100 XP ke level {{ $summary['level'] + 1 }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">🔥 Streak</div>
                    <div class="stat-value">{{ $summary['streak'] }} hari</div>
                    <div class="mt-2 text-xs text-ink-muted">Rekor terpanjang: {{ $summary['longest_streak'] }} hari</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Sesi Latihan</div>
                    <div class="stat-value">{{ $summary['sessions'] }}</div>
                    <div class="mt-2 text-xs text-ink-muted">Rata-rata skor: {{ $summary['avg_score'] }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-label">Lesson Dikuasai</div>
                    <div class="stat-value">{{ $summary['lessons_mastered'] }}<span class="text-base text-ink-muted">/{{ $summary['lessons_total'] }}</span></div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                        @php
                            $lessonPercent = $summary['lessons_total'] > 0
                                ? (int) round($summary['lessons_mastered'] / $summary['lessons_total'] * 100)
                                : 0;
                        @endphp
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $lessonPercent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                {{-- Progress per kategori --}}
                <div class="card lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-ink">Progress Belajar</h3>
                        <a href="{{ route('student.progress') }}" class="text-sm font-semibold text-primary hover:underline">Lihat semua →</a>
                    </div>

                    <div class="mt-4 space-y-4">
                        @forelse ($progress as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-ink">{{ $row['category']->icon }} {{ $row['category']->name }}</span>
                                    <span class="text-ink-muted">{{ $row['done'] }}/{{ $row['total'] }} lesson</span>
                                </div>
                                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada kategori pembelajaran.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Rekomendasi lesson --}}
                <div class="card">
                    <h3 class="font-extrabold text-ink">✨ Rekomendasi Latihan</h3>
                    <p class="mt-1 text-sm text-ink-muted">Lesson yang perlu kamu latih berikutnya.</p>

                    <div class="mt-4 space-y-3">
                        @forelse ($recommended as $lesson)
                            <div class="rounded-lg border border-surface-border p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-bold text-ink">{{ $lesson->title }}</div>
                                        <div class="text-xs text-ink-muted">{{ $lesson->material->title }}</div>
                                    </div>
                                    <span class="badge badge-indigo !text-xs">+{{ $lesson->xp_reward }} XP</span>
                                </div>
                                <a href="{{ route('student.practice.start', $lesson) }}"
                                   class="btn-secondary mt-2 w-full !py-1.5 text-xs">Latih sekarang →</a>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada lesson. Tambahkan konten via seeder.</p>
                        @endforelse
                    </div>

                    <a href="{{ route('student.practice.index') }}" class="btn-primary mt-4 w-full text-center">
                        ✋ Buka AI Practice
                    </a>
                </div>
            </div>

            {{-- Aktivitas terakhir --}}
            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Aktivitas Terakhir</h3>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Kategori</th>
                                <th class="py-2 pr-4">Skor</th>
                                <th class="py-2 pr-4">XP</th>
                                <th class="py-2">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSessions as $session)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4 font-semibold text-ink">{{ $session->lesson->title }}</td>
                                    <td class="py-2.5 pr-4 text-ink-muted">{{ $session->lesson->material->category->name }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="badge {{ $session->score >= 60 ? 'badge-teal' : 'badge-amber' }}">{{ $session->score }}</span>
                                    </td>
                                    <td class="py-2.5 pr-4 text-ink-muted">+{{ $session->xp_earned }}</td>
                                    <td class="py-2.5 text-ink-muted">{{ $session->completed_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-ink-muted">
                                        Belum ada latihan — mulai dari materi pertama!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
