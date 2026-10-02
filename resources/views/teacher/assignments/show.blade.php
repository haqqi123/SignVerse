<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('teacher.assignments.index') }}" class="hover:text-ink">Assignment</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $assignment->title }}</span>
        </div>
        <h2 class="section-title mt-1">{{ $assignment->title }}</h2>
        <p class="section-sub">
            Lesson: {{ $assignment->lesson->title }}
            · Syarat ≥ {{ $assignment->min_score }}
            · +{{ $assignment->xp_reward }} XP
            @if ($assignment->due_at)
                · Deadline {{ $assignment->due_at->format('d M Y H:i') }}
            @endif
        </p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            {{-- Statistik --}}
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">Total Siswa</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">✅ Selesai</div>
                    <div class="stat-value">{{ $stats['completed'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">⏳ Belum / Proses</div>
                    <div class="stat-value">{{ $stats['assigned'] + $stats['in_progress'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Rata-rata Skor</div>
                    <div class="stat-value">{{ $stats['avg_score'] ?? '—' }}</div>
                </div>
            </div>

            @if ($assignment->description)
                <div class="card mt-6">
                    <h3 class="text-sm font-extrabold text-ink">Instruksi</h3>
                    <p class="mt-1 whitespace-pre-line text-sm text-ink-muted">{{ $assignment->description }}</p>
                </div>
            @endif

            {{-- Daftar pengerjaan siswa --}}
            <div class="card mt-6 overflow-x-auto">
                <h3 class="font-extrabold text-ink">Pengerjaan Siswa</h3>
                <table class="mt-4 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-4">Siswa</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Skor</th>
                            <th class="py-2">Dikumpulkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignment->submissions->sortBy('student.user.name') as $submission)
                            <tr class="border-b border-surface-border/60">
                                <td class="py-2.5 pr-4 font-semibold text-ink">{{ $submission->student->user->name }}</td>
                                <td class="py-2.5 pr-4">
                                    @if ($submission->status === \App\Models\AssignmentSubmission::STATUS_COMPLETED)
                                        <span class="badge badge-teal !text-xs">Selesai</span>
                                    @elseif($submission->status === \App\Models\AssignmentSubmission::STATUS_IN_PROGRESS)
                                        <span class="badge badge-amber !text-xs">Proses</span>
                                    @else
                                        <span class="badge badge-gray !text-xs">Belum</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">{{ $submission->score ?? '—' }}</td>
                                <td class="py-2.5 text-ink-muted">{{ $submission->submitted_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-ink-muted">Belum ada siswa terdistribusi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('teacher.assignments.index') }}" class="btn-secondary">← Kembali</a>
                <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}"
                      onsubmit="return confirm('Hapus tugas ini beserta seluruh pengerjaan siswa?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Hapus tugas</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
