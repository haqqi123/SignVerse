<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('student.materials.index') }}" class="hover:text-ink">Materi Belajar</a>
            <span>/</span>
            <span class="font-semibold text-ink">{{ $material->category->name }}</span>
        </div>
        <h2 class="section-title mt-1">{{ $material->title }}</h2>
        <p class="section-sub">
            <span class="badge {{ $material->language === 'sibi' ? 'badge-indigo' : 'badge-teal' }} uppercase">{{ $material->language }}</span>
            Tingkat {{ $material->difficulty }}/5 · {{ $material->lessons->count() }} lesson
        </p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

            @if ($material->description)
                <div class="card">
                    <p class="text-sm text-ink-muted">{{ $material->description }}</p>
                </div>
            @endif

            <div class="mt-6 space-y-3">
                @forelse ($material->lessons as $lesson)
                    <div class="card flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-extrabold text-ink">{{ $lesson->title }}</h3>
                            <p class="mt-0.5 text-xs text-ink-muted">
                                Gesture: <code class="rounded bg-gray-100 px-1.5 py-0.5">{{ $lesson->gesture_label }}</code>
                                · Tingkat {{ $lesson->difficulty }}/5
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="badge badge-indigo">+{{ $lesson->xp_reward }} XP</span>
                            <a href="{{ route('student.practice.start', $lesson) }}" class="btn-primary !py-2 text-xs">
                                ✋ Latih
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="card text-center">
                        <p class="text-ink-muted">Belum ada lesson di materi ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
