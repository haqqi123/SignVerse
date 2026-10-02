<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Dashboard Guru</h2>
        <p class="section-sub">Selamat datang, {{ $user->name }} 👋</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Statistik kelas --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">👥 Jumlah Siswa</div>
                    <div class="stat-value">{{ $overview['students'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🏋️ Latihan Minggu Ini</div>
                    <div class="stat-value">{{ $overview['weekly_sessions'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🎯 Rata-rata Kelas</div>
                    <div class="stat-value">{{ $overview['avg_class_score'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">📋 Assignment Aktif</div>
                    <div class="stat-value">{{ $overview['active_assignments'] }}</div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                {{-- Monitoring siswa singkat --}}
                <div class="card overflow-x-auto">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-ink">Aktivitas Siswa</h3>
                        <a href="{{ route('teacher.students.index') }}" class="text-xs font-semibold text-primary hover:underline">Lihat semua →</a>
                    </div>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Siswa</th>
                                <th class="py-2 pr-4">Latihan</th>
                                <th class="py-2 pr-4">Rata-rata</th>
                                <th class="py-2">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $row)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4">
                                        <a href="{{ route('teacher.students.show', $row['student']) }}" class="font-bold text-ink hover:text-primary">
                                            {{ $row['name'] }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 pr-4 text-ink-muted">{{ $row['sessions'] }} sesi</td>
                                    <td class="py-2.5 pr-4">{{ $row['avg_score'] }}</td>
                                    <td class="py-2.5 text-ink-muted">{{ $row['last_activity']?->diffForHumans() ?? 'Belum ada' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-ink-muted">Belum ada siswa terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Analisis error gesture --}}
                <div class="card overflow-x-auto">
                    <h3 class="font-extrabold text-ink">Gesture Sering Salah</h3>
                    <p class="mt-1 text-xs text-ink-muted">Dari hasil recognition kelas — gunakan untuk menentukan materi ulang.</p>
                    <table class="mt-3 w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                                <th class="py-2 pr-4">Lesson</th>
                                <th class="py-2 pr-4">Gesture</th>
                                <th class="py-2 pr-4">Salah</th>
                                <th class="py-2">Akurasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($errorAnalysis as $row)
                                <tr class="border-b border-surface-border/60">
                                    <td class="py-2.5 pr-4 font-semibold text-ink">{{ $row['lesson'] }}</td>
                                    <td class="py-2.5 pr-4"><span class="badge badge-amber !text-xs">{{ $row['gesture'] }}</span></td>
                                    <td class="py-2.5 pr-4">{{ $row['wrong'] }}/{{ $row['total'] }}</td>
                                    <td class="py-2.5">
                                        <span class="badge {{ $row['accuracy'] >= 60 ? 'badge-teal' : 'badge-amber' }} !text-xs">{{ $row['accuracy'] }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-ink-muted">Belum ada data error — siswa belum mulai berlatih.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Assignment aktif & quick actions --}}
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="card">
                    <h3 class="font-extrabold text-ink">Assignment Aktif</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($activeAssignments as $assignment)
                            <li class="flex items-center justify-between rounded-lg bg-surface px-3 py-2">
                                <a href="{{ route('teacher.assignments.show', $assignment) }}" class="font-semibold text-ink hover:text-primary">
                                    {{ $assignment->title }}
                                </a>
                                <span class="text-xs text-ink-muted">
                                    {{ $assignment->due_at ? 'Deadline '.$assignment->due_at->format('d M') : 'Tanpa deadline' }}
                                </span>
                            </li>
                        @empty
                            <li class="text-sm text-ink-muted">Tidak ada assignment aktif.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="card">
                    <h3 class="font-extrabold text-ink">Quick Actions</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <a href="{{ route('teacher.students.index') }}" class="btn-primary">👥 Monitoring Siswa</a>
                        <a href="{{ route('teacher.assignments.create') }}" class="btn-secondary">+ Buat Tugas</a>
                        <a href="{{ route('teacher.assignments.index') }}" class="btn-secondary">📋 Kelola Assignment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
