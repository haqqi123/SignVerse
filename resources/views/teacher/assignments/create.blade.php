<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-ink-muted">
            <a href="{{ route('teacher.assignments.index') }}" class="hover:text-ink">Assignment</a>
            <span>/</span>
            <span class="font-semibold text-ink">Buat Tugas</span>
        </div>
        <h2 class="section-title mt-1">Buat Tugas Baru</h2>
        <p class="section-sub">Tugas otomatis dibagikan ke semua siswa yang terdaftar.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('teacher.assignments.store') }}" class="card space-y-5">
                @csrf

                <div>
                    <label for="lesson_id" class="mb-1 block text-sm font-bold text-ink">Lesson Target</label>
                    <select id="lesson_id" name="lesson_id" required
                            class="w-full rounded-lg border border-surface-border bg-white px-3 py-2.5 text-sm text-ink">
                        <option value="">— Pilih lesson —</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}" @old('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                [{{ $lesson->material->category->name }}] {{ $lesson->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('lesson_id') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="mb-1 block text-sm font-bold text-ink">Judul Tugas</label>
                    <input id="title" name="title" type="text" required maxlength="200"
                           value="{{ old('title') }}"
                           placeholder="cth: Latihan Sapaan Dasar"
                           class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink" />
                    @error('title') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="mb-1 block text-sm font-bold text-ink">Instruksi (opsional)</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Petunjuk pengerjaan untuk siswa…"
                              class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink">{{ old('description') }}</textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="min_score" class="mb-1 block text-sm font-bold text-ink">Syarat Skor Lulus</label>
                        <input id="min_score" name="min_score" type="number" min="1" max="100" required
                               value="{{ old('min_score', 60) }}"
                               class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink" />
                        <p class="mt-1 text-xs text-ink-muted">Skor latihan minimal agar tugas dianggap selesai.</p>
                        @error('min_score') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="xp_reward" class="mb-1 block text-sm font-bold text-ink">XP Reward</label>
                        <input id="xp_reward" name="xp_reward" type="number" min="1" max="500" required
                               value="{{ old('xp_reward', 30) }}"
                               class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink" />
                        @error('xp_reward') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="available_at" class="mb-1 block text-sm font-bold text-ink">Tersedia Mulai (opsional)</label>
                        <input id="available_at" name="available_at" type="datetime-local"
                               value="{{ old('available_at') }}"
                               class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink" />
                    </div>
                    <div>
                        <label for="due_at" class="mb-1 block text-sm font-bold text-ink">Deadline (opsional)</label>
                        <input id="due_at" name="due_at" type="datetime-local"
                               value="{{ old('due_at') }}"
                               class="w-full rounded-lg border border-surface-border px-3 py-2.5 text-sm text-ink" />
                        @error('due_at') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Buat & Bagikan</button>
                    <a href="{{ route('teacher.assignments.index') }}" class="text-sm font-semibold text-ink-muted hover:text-ink">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
