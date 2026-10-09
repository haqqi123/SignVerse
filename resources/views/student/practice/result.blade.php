<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Hasil Latihan</h2>
        <p class="section-sub">{{ $session->lesson->title }} · {{ $session->lesson->material->category->name }}</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

            <div class="card text-center">
                <div class="text-6xl">{{ $passed ? '🎉' : '💪' }}</div>
                <h3 class="mt-3 text-2xl font-extrabold text-ink">
                    {{ $passed ? 'Lulus!' : 'Belum Lulus' }}
                </h3>
                <p class="mt-1 text-sm text-ink-muted">
                    {{ $session->attempts }} attempt · {{ $session->completed_at?->diffForHumans() }}
                </p>

                <div class="mx-auto mt-6 grid max-w-md grid-cols-3 gap-4">
                    <div>
                        <div class="stat-label">Skor</div>
                        <div class="text-3xl font-extrabold {{ $passed ? 'text-teal-600' : 'text-amber-600' }}">{{ $session->score }}</div>
                    </div>
                    <div>
                        <div class="stat-label">XP Didapat</div>
                        <div class="text-3xl font-extrabold text-primary">+{{ $session->xp_earned }}</div>
                    </div>
                    <div>
                        <div class="stat-label">Attempt</div>
                        <div class="text-3xl font-extrabold text-ink">{{ $session->attempts }}</div>
                    </div>
                </div>

                <div class="mt-4 text-xs text-ink-muted">Syarat lulus: skor ≥ 60</div>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('student.practice.start', $session->lesson) }}" class="btn-primary">
                        🔄 Latih Lagi
                    </a>
                    <a href="{{ route('student.practice.index') }}" class="btn-secondary">
                        Pilih Lesson Lain
                    </a>
                    <a href="{{ route('student.dashboard') }}" class="btn-secondary">
                        Ke Dashboard
                    </a>
                </div>
            </div>

            {{-- Rincian attempt --}}
            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Rincian Percobaan</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">#</th>
                                <th class="py-2 pr-4">Diharapkan</th>
                                <th class="py-2 pr-4">Terdeteksi</th>
                                <th class="py-2 pr-4">Confidence</th>
                                <th class="py-2">Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($session->gestureResults as $i => $result)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2 pr-4 text-ink-muted">{{ $i + 1 }}</td>
                                    <td class="py-2 pr-4"><code class="rounded bg-gray-100 px-1.5 py-0.5">{{ $result->expected_gesture }}</code></td>
                                    <td class="py-2 pr-4">{{ $result->recognized_gesture ?? '—' }}</td>
                                    <td class="py-2 pr-4 text-ink-muted">{{ $result->confidence }}%</td>
                                    <td class="py-2">
                                        <span class="badge {{ $result->correct ? 'badge-teal' : 'badge-amber' }}">{{ $result->correct ? 'Benar' : 'Salah' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-4 text-center text-ink-muted">Tidak ada attempt.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
