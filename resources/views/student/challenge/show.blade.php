<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Challenge Harian</h2>
        <p class="section-sub">{{ today()->translatedFormat('l, d F Y') }} — satu lesson spesial, XP bonus!</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

            <div class="card text-center">
                <span class="text-5xl">🎯</span>
                <h3 class="mt-3 text-xl font-extrabold text-ink">{{ $challenge->title }}</h3>

                @if ($challenge->description)
                    <p class="mt-2 text-sm text-ink-muted">{{ $challenge->description }}</p>
                @endif

                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <span class="badge badge-indigo">+{{ $challenge->xp_reward }} XP bonus</span>
                    <span class="badge {{ $attempt?->passed ? 'badge-teal' : 'badge-amber' }}">
                        Syarat lulus: skor ≥ {{ \App\Services\GamificationService::CHALLENGE_PASS_SCORE }}
                    </span>
                </div>

                <div class="mx-auto mt-6 max-w-sm rounded-lg border border-surface-border bg-gray-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-ink-muted">Lesson hari ini</div>
                    <div class="mt-1 text-lg font-extrabold text-ink">{{ $challenge->lesson->title }}</div>
                    <div class="text-xs text-ink-muted">
                        {{ $challenge->lesson->material->category->name }} · {{ strtoupper($challenge->lesson->material->language) }}
                    </div>
                </div>

                @if ($attempt)
                    {{-- Sudah ikut hari ini --}}
                    <div class="mt-6 rounded-lg border border-surface-border p-4">
                        <div class="text-sm font-bold text-ink">Hasilmu hari ini</div>
                        <div class="mt-2 flex items-center justify-center gap-6">
                            <div>
                                <div class="stat-label">Skor</div>
                                <div class="text-2xl font-extrabold {{ $attempt->passed ? 'text-teal-600' : 'text-amber-600' }}">{{ $attempt->score }}</div>
                            </div>
                            <div>
                                <div class="stat-label">XP</div>
                                <div class="text-2xl font-extrabold text-primary">+{{ $attempt->xp_earned }}</div>
                            </div>
                            <div>
                                <div class="stat-label">Status</div>
                                <div class="text-2xl">{{ $attempt->passed ? '🎉' : '💪' }}</div>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-ink-muted">Challenge berikutnya tersedia besok!</p>
                    </div>
                @else
                    {{-- Belum ikut — tombol attempt (evaluasi mock, kamera nyata di phase 9) --}}
                    <form method="POST" action="{{ route('student.challenge.attempt') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="btn-primary w-full sm:w-auto">
                            ✋ Ikuti Challenge ({{ \App\Services\GamificationService::CHALLENGE_PASS_SCORE }}+ = lulus)
                        </button>
                        <p class="mt-2 text-xs text-ink-muted">
                            Mode development: 3 attempt gesture dievaluasi mock AI. Kamera nyata di Phase 9.
                        </p>
                    </form>
                @endif
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('student.practice.index') }}" class="text-sm font-semibold text-primary hover:underline">
                    Ingin latihan biasa dulu? Buka AI Practice →
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
