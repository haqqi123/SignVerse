<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Progress Belajar</h2>
        <p class="section-sub">Pantau perkembanganmu di setiap kategori.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Ringkasan singkat --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="stat-card">
                    <div class="stat-label">Level & XP</div>
                    <div class="stat-value">{{ $summary['level'] }}</div>
                    <div class="text-xs text-ink-muted">{{ number_format($summary['xp']) }} XP total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Lesson Dikuasai</div>
                    <div class="stat-value">{{ $summary['lessons_mastered'] }}<span class="text-base text-ink-muted">/{{ $summary['lessons_total'] }}</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Rata-rata Skor</div>
                    <div class="stat-value">{{ $summary['avg_score'] }}</div>
                </div>
            </div>

            {{-- Progress per kategori --}}
            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Progress per Kategori</h3>

                <div class="mt-4 space-y-4">
                    @forelse ($progress as $row)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-ink">{{ $row['category']->icon }} {{ $row['category']->name }}</span>
                                <span class="text-ink-muted">{{ $row['done'] }}/{{ $row['total'] }} lesson ({{ $row['percent'] }}%)</span>
                            </div>
                            <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $row['percent'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted">Belum ada kategori pembelajaran.</p>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat latihan --}}
            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Riwayat Latihan</h3>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Materi</th>
                                <th class="py-2 pr-4">Skor</th>
                                <th class="py-2 pr-4">Percobaan</th>
                                <th class="py-2 pr-4">XP</th>
                                <th class="py-2">Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sessions as $session)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4 font-semibold text-ink">{{ $session->lesson->title }}</td>
                                    <td class="py-2.5 pr-4 text-ink-muted">{{ $session->lesson->material->title }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="badge {{ $session->score >= 60 ? 'badge-teal' : 'badge-amber' }}">{{ $session->score }}</span>
                                    </td>
                                    <td class="py-2.5 pr-4 text-ink-muted">{{ $session->attempts }}×</td>
                                    <td class="py-2.5 pr-4 text-ink-muted">+{{ $session->xp_earned }}</td>
                                    <td class="py-2.5 text-ink-muted">{{ $session->completed_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-ink-muted">Belum ada latihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
