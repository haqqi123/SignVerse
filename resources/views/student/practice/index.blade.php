<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">AI Practice</h2>
        <p class="section-sub">Latih isyarat dengan kamera — skor dan umpan balik otomatis.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                💡 Mode development: deteksi gesture masih <b>mock</b> — AI kamera nyata menyusul di Phase 9.
            </div>

            @forelse ($categories as $category)
                <div class="mb-8">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $category->icon }}</span>
                        <h3 class="text-lg font-extrabold text-ink">{{ $category->name }}</h3>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->materials as $material)
                            @foreach ($material->lessons as $lesson)
                                @php
                                    $best = $bestScores[$lesson->id] ?? null;
                                @endphp
                                <div class="card flex items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-ink">{{ $lesson->title }}</div>
                                        <div class="text-xs text-ink-muted">{{ $material->title }} · +{{ $lesson->xp_reward }} XP</div>
                                        @if ($best !== null)
                                            <span class="badge {{ $best >= 60 ? 'badge-teal' : 'badge-amber' }} mt-1 !text-xs">
                                                Skor terbaik: {{ $best }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('student.practice.start', $lesson) }}" class="btn-primary shrink-0 !py-2 text-xs">
                                        ✋ Latih
                                    </a>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card text-center">
                    <p class="text-ink-muted">Belum ada lesson untuk dilatih.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
