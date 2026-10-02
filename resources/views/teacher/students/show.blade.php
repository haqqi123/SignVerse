<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('teacher.students.index') }}" class="hover:text-ink">Monitoring Siswa</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $detail['summary']['name'] }}</span>
        </div>
        <h2 class="section-title mt-1">Detail Siswa</h2>
        <p class="section-sub">Statistik, progress, dan area yang perlu diperbaiki.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @php($summary = $detail['summary'])

            {{-- Ringkasan siswa --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">🏅 Level / XP</div>
                    <div class="stat-value">Lv {{ $summary['level'] }}</div>
                    <div class="mt-1 text-xs text-ink-muted">{{ $summary['xp'] }} XP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🔥 Streak</div>
                    <div class="stat-value">{{ $summary['streak'] }} hari</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🏋️ Sesi Latihan</div>
                    <div class="stat-value">{{ $summary['sessions'] }}</div>
                    <div class="mt-1 text-xs text-ink-muted">Rata-rata skor {{ $summary['avg_score'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">📅 Aktivitas Terakhir</div>
                    <div class="stat-value !text-lg">{{ $summary['last_activity']?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                {{-- Progress per kategori --}}
                <div class="card">
                    <h3 class="font-extrabold text-ink">Progress per Kategori</h3>
                    <div class="mt-3 space-y-3">
                        @forelse ($detail['category_progress'] as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-ink">{{ $row['category']->name }}</span>
                                    <span class="text-ink-muted">{{ $row['done'] }}/{{ $row['total'] }} lesson</span>
                                </div>
                                <div class="mt-1 h-2 w-full rounded-full bg-surface-border">
                                    <div class="h-2 rounded-full bg-primary" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Gesture sering salah --}}
                <div class="card overflow-x-auto">
                    <h3 class="font-extrabold text-ink">Gesture Sering Salah</h3>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Gesture</th>
                                <th class="py-2 pr-4">Salah</th>
                                <th class="py-2">Akurasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($errorAnalysis as $row)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4 font-semibold text-ink">{{ $row['lesson'] }}</td>
                                    <td class="py-2.5 pr-4"><span class="badge badge-amber !text-xs">{{ $row['gesture'] }}</span></td>
                                    <td class="py-2.5 pr-4">{{ $row['wrong'] }}/{{ $row['total'] }}</td>
                                    <td class="py-2.5">
                                        <span class="badge {{ $row['accuracy'] >= 60 ? 'badge-teal' : 'badge-amber' }} !text-xs">{{ $row['accuracy'] }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-ink-muted">Tidak ada gesture yang bermasalah. 👍</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                {{-- Sesi terakhir --}}
                <div class="card overflow-x-auto">
                    <h3 class="font-extrabold text-ink">Sesi Latihan Terakhir</h3>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Skor</th>
                                <th class="py-2">Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detail['recent_sessions'] as $session)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4 font-semibold text-ink">{{ $session->lesson->title }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="badge {{ $session->score >= 60 ? 'badge-teal' : 'badge-amber' }} !text-xs">{{ $session->score }}</span>
                                    </td>
                                    <td class="py-2.5 text-ink-muted">{{ $session->completed_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-ink-muted">Belum ada sesi latihan selesai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Achievement --}}
                <div class="card">
                    <h3 class="font-extrabold text-ink">Achievement</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($detail['achievements'] as $achievement)
                            <span class="badge badge-indigo !text-xs" title="{{ $achievement->description }}">
                                🏆 {{ $achievement->name }}
                            </span>
                        @empty
                            <p class="text-sm text-ink-muted">Belum ada achievement.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('teacher.students.index') }}" class="btn-secondary">← Kembali</a>
            </div>
        </div>
    </div>
</x-app-layout>
