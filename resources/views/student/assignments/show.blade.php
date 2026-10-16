<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('student.assignments.index') }}" class="hover:text-ink">Assignment</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $assignment->title }}</span>
        </div>
        <h2 class="section-title mt-1">{{ $assignment->title }}</h2>
        <p class="section-sub">
            dari {{ $assignment->teacher->user->name }}
            · Syarat skor ≥ {{ $assignment->min_score }}
            · Reward +{{ $assignment->xp_reward }} XP
            @if ($assignment->due_at)
                · Deadline {{ $assignment->due_at->format('d M Y H:i') }}
            @endif
        </p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">

            @if ($submission->status === \App\Models\AssignmentSubmission::STATUS_COMPLETED)
                {{-- Sudah selesai --}}
                <div class="card text-center">
                    <div class="text-5xl">🎉</div>
                    <h3 class="mt-2 text-xl font-extrabold text-ink">Tugas Selesai!</h3>
                    <div class="mx-auto mt-4 flex max-w-sm items-center justify-center gap-8">
                        <div>
                            <div class="stat-label">Skor</div>
                            <div class="text-3xl font-extrabold text-teal-600">{{ $submission->score }}</div>
                        </div>
                        <div>
                            <div class="stat-label">XP</div>
                            <div class="text-3xl font-extrabold text-primary">+{{ $submission->xp_earned }}</div>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-ink-muted">Diselesaikan {{ $submission->completed_at?->diffForHumans() }}</p>
                </div>
            @else
                {{-- Belum selesai --}}
                <div class="card">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-ink">Cara Pengerjaan</h3>
                        <span class="badge {{ $submission->status === \App\Models\AssignmentSubmission::STATUS_IN_PROGRESS ? 'badge-amber' : 'badge-gray' }} !text-xs">
                            {{ $submission->status === \App\Models\AssignmentSubmission::STATUS_IN_PROGRESS ? 'Skor belum cukup' : 'Belum dimulai' }}
                        </span>
                    </div>

                    <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-ink-muted">
                        <li>Buka lesson <b class="text-ink">{{ $assignment->lesson->title }}</b> di AI Practice.</li>
                        <li>Latihan sampai sesi selesai dengan skor ≥ <b class="text-ink">{{ $assignment->min_score }}</b>.</li>
                        <li>Kumpulkan sesi latihan terbaikmu di bawah ini.</li>
                    </ol>

                    @if ($assignment->description)
                        <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-ink-muted">
                            <b class="text-ink">Instruksi guru:</b> {{ $assignment->description }}
                        </div>
                    @endif
                </div>

                {{-- Riwayat sesi latihan lesson ini --}}
                @php
                    $sessions = auth()->user()->student
                        ->practiceSessions()
                        ->where('lesson_id', $assignment->lesson_id)
                        ->where('status', \App\Models\PracticeSession::STATUS_COMPLETED)
                        ->orderByDesc('completed_at')
                        ->limit(5)
                        ->get();
                @endphp
                <div class="card mt-6">
                    <h3 class="font-extrabold text-ink">Kumpulkan Sesi Latihan</h3>
                    <p class="mt-1 text-xs text-ink-muted">Pilih sesi latihan yang sudah selesai untuk dinilai.</p>

                    <div class="mt-4 space-y-2">
                        @forelse ($sessions as $session)
                            <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}"
                                  class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-surface-border p-3">
                                @csrf
                                <input type="hidden" name="practice_session_id" value="{{ $session->id }}" />
                                <div class="text-sm">
                                    <span class="badge {{ $session->score >= $assignment->min_score ? 'badge-teal' : 'badge-amber' }}">
                                        Skor {{ $session->score }}
                                    </span>
                                    <span class="ml-2 text-ink-muted">{{ $session->completed_at?->diffForHumans() }}</span>
                                </div>
                                <button type="submit" class="btn-primary !py-1.5 text-xs">Kumpulkan</button>
                            </form>
                        @empty
                            <p class="text-sm text-ink-muted">
                                Belum ada sesi latihan selesai untuk lesson ini.
                                <a href="{{ route('student.practice.start', $assignment->lesson) }}" class="font-semibold text-primary hover:underline">Mulai latihan sekarang →</a>
                            </p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('student.assignments.index') }}" class="btn-secondary">← Semua Tugas</a>
            </div>
        </div>
    </div>
</x-app-layout>
