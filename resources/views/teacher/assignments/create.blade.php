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
                    <label for="lesson_id" class="form-label">Lesson Target</label>
                    <select id="lesson_id" name="lesson_id" required
                            class="form-input">
                        <option value="">— Pilih lesson —</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}" @selected(old('lesson_id') == $lesson->id)>
                                [{{ $lesson->material->category->name }}] {{ $lesson->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('lesson_id') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="form-label">Judul Tugas</label>
                    <input id="title" name="title" type="text" required maxlength="200"
                           value="{{ old('title') }}"
                           placeholder="cth: Latihan Sapaan Dasar"
                           class="form-input" />
                    @error('title') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Instruksi (opsional)</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Petunjuk pengerjaan untuk siswa…"
                              class="form-input">{{ old('description') }}</textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="min_score" class="form-label">Syarat Skor Lulus</label>
                        <input id="min_score" name="min_score" type="number" min="1" max="100" required
                               value="{{ old('min_score', 60) }}"
                               class="form-input" />
                        <p class="mt-1 text-xs text-ink-muted">Skor latihan minimal agar tugas dianggap selesai.</p>
                        @error('min_score') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="xp_reward" class="form-label">XP Reward</label>
                        <input id="xp_reward" name="xp_reward" type="number" min="1" max="500" required
                               value="{{ old('xp_reward', 30) }}"
                               class="form-input" />
                        @error('xp_reward') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="available_at" class="form-label">Tersedia Mulai (opsional)</label>
                        <input id="available_at" name="available_at" type="datetime-local"
                               value="{{ old('available_at') }}"
                               class="form-input" />
                    </div>
                    <div>
                        <label for="due_at" class="form-label">Deadline (opsional)</label>
                        <input id="due_at" name="due_at" type="datetime-local"
                               value="{{ old('due_at') }}"
                               class="form-input" />
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
