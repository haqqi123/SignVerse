<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="section-title">Assignment</h2>
                <p class="section-sub">Kelola tugas latihan untuk siswa.</p>
            </div>
            <a href="{{ route('teacher.assignments.create') }}" class="btn-primary">+ Buat Tugas</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="card overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-4">Judul</th>
                            <th class="py-2 pr-4">Lesson</th>
                            <th class="py-2 pr-4">Syarat</th>
                            <th class="py-2 pr-4">XP</th>
                            <th class="py-2 pr-4">Deadline</th>
                            <th class="py-2">Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            <tr class="border-b border-surface-border/60">
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('teacher.assignments.show', $assignment) }}" class="font-bold text-ink hover:text-primary">
                                        {{ $assignment->title }}
                                    </a>
                                </td>
                                <td class="py-2.5 pr-4 text-ink-muted">{{ $assignment->lesson->title }}</td>
                                <td class="py-2.5 pr-4"><span class="badge badge-amber !text-xs">≥ {{ $assignment->min_score }}</span></td>
                                <td class="py-2.5 pr-4 text-ink-muted">+{{ $assignment->xp_reward }}</td>
                                <td class="py-2.5 pr-4 text-ink-muted">{{ $assignment->due_at?->format('d M Y') ?? '—' }}</td>
                                <td class="py-2.5">{{ $assignment->submissions_count }} siswa</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-ink-muted">
                                    Belum ada tugas. <a href="{{ route('teacher.assignments.create') }}" class="font-semibold text-primary hover:underline">Buat tugas pertama →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
