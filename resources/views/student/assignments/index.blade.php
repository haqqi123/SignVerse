<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Assignment</h2>
        <p class="section-sub">Tugas latihan dari gurumu — kerjakan lewat AI Practice.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            {{-- Belum selesai --}}
            <h3 class="font-extrabold text-ink">⏳ Perlu Dikerjakan</h3>
            <div class="mt-3 space-y-3">
                @forelse ($pending as $assignment)
                    @php
                        $submission = $assignment->submissions->first();
                    @endphp
                    <div class="card flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <a href="{{ route('student.assignments.show', $assignment) }}" class="font-extrabold text-ink hover:text-primary">
                                {{ $assignment->title }}
                            </a>
                            <div class="mt-0.5 text-xs text-ink-muted">
                                {{ $assignment->lesson->material->category->name }} · {{ $assignment->lesson->title }}
                                · dari {{ $assignment->teacher->user->name }}
                            </div>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <span class="badge badge-indigo !text-xs">+{{ $assignment->xp_reward }} XP</span>
                                <span class="badge badge-amber !text-xs">Syarat ≥ {{ $assignment->min_score }}</span>
                                @if ($assignment->due_at)
                                    <span class="badge badge-gray !text-xs">Deadline {{ $assignment->due_at->diffForHumans() }}</span>
                                @endif
                                @if ($submission?->status === \App\Models\AssignmentSubmission::STATUS_IN_PROGRESS)
                                    <span class="badge badge-amber !text-xs">Skor terakhir: {{ $submission->score }} — belum lulus</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('student.assignments.show', $assignment) }}" class="btn-primary shrink-0 !py-2 text-xs">
                            Kerjakan →
                        </a>
                    </div>
                @empty
                    <div class="card text-center">
                        <p class="text-ink-muted">Tidak ada tugas yang perlu dikerjakan. 🎉</p>
                    </div>
                @endforelse
            </div>

            {{-- Selesai --}}
            @if ($completed->isNotEmpty())
                <h3 class="mt-8 font-extrabold text-ink">✅ Selesai</h3>
                <div class="card mt-3 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Tugas</th>
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Skor</th>
                                <th class="py-2 pr-4">XP</th>
                                <th class="py-2">Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($completed as $assignment)
                                @php
                                    $submission = $assignment->submissions->first();
                                @endphp
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4">
                                        <a href="{{ route('student.assignments.show', $assignment) }}" class="font-bold text-ink hover:text-primary">
                                            {{ $assignment->title }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 pr-4 text-ink-muted">{{ $assignment->lesson->title }}</td>
                                    <td class="py-2.5 pr-4"><span class="badge badge-teal !text-xs">{{ $submission->score }}</span></td>
                                    <td class="py-2.5 pr-4 text-ink-muted">+{{ $submission->xp_earned }}</td>
                                    <td class="py-2.5 text-ink-muted">{{ $submission->completed_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
