<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Materi Belajar</h2>
        <p class="section-sub">Pelajari isyarat SIBI & BISINDO per kategori.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Filter bahasa --}}
            <div class="mb-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('student.materials.index') }}"
                   class="badge {{ ! $language ? 'badge-indigo' : 'badge-gray' }} cursor-pointer">Semua</a>
                <a href="{{ route('student.materials.index', ['language' => 'sibi']) }}"
                   class="badge {{ $language === 'sibi' ? 'badge-indigo' : 'badge-gray' }} cursor-pointer">SIBI</a>
                <a href="{{ route('student.materials.index', ['language' => 'bisindo']) }}"
                   class="badge {{ $language === 'bisindo' ? 'badge-indigo' : 'badge-gray' }} cursor-pointer">BISINDO</a>
            </div>

            @forelse ($categories as $category)
                <div class="mb-8">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $category->icon }}</span>
                        <h3 class="text-lg font-extrabold text-ink">{{ $category->name }}</h3>
                    </div>
                    @if ($category->description)
                        <p class="mt-1 text-sm text-ink-muted">{{ $category->description }}</p>
                    @endif

                    <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($category->materials as $material)
                            <a href="{{ route('student.materials.show', $material) }}"
                               class="card group transition hover:border-primary/40 hover:shadow-md">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-extrabold text-ink group-hover:text-primary">{{ $material->title }}</h4>
                                    <span class="badge {{ $material->language === 'sibi' ? 'badge-indigo' : 'badge-teal' }} !text-xs uppercase">
                                        {{ $material->language }}
                                    </span>
                                </div>

                                @if ($material->description)
                                    <p class="mt-2 line-clamp-2 text-sm text-ink-muted">{{ $material->description }}</p>
                                @endif

                                <div class="mt-3 flex items-center justify-between text-xs text-ink-muted">
                                    <span>📖 {{ $material->lessons_count }} lesson</span>
                                    <span>Tingkat {{ $material->difficulty }}/5</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card text-center">
                    <p class="text-ink-muted">Belum ada materi untuk filter ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
