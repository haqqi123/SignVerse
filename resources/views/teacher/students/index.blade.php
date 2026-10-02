<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Monitoring Siswa</h2>
        <p class="section-sub">Ringkasan aktivitas latihan seluruh siswa.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <div class="card overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-surface-border text-xs uppercase tracking-wider text-ink-muted">
                            <th class="py-2 pr-4">Siswa</th>
                            <th class="py-2 pr-4">Level</th>
                            <th class="py-2 pr-4">XP</th>
                            <th class="py-2 pr-4">Streak</th>
                            <th class="py-2 pr-4">Latihan</th>
                            <th class="py-2 pr-4">Rata-rata Skor</th>
                            <th class="py-2">Aktivitas Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $row)
                            <tr class="border-b border-surface-border/60">
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('teacher.students.show', $row['student']) }}"
                                       class="font-bold text-ink hover:text-primary">
                                        {{ $row['name'] }}
                                    </a>
                                </td>
                                <td class="py-2.5 pr-4"><span class="badge badge-indigo !text-xs">Lv {{ $row['level'] }}</span></td>
                                <td class="py-2.5 pr-4 text-ink-muted">{{ $row['xp'] }}</td>
                                <td class="py-2.5 pr-4">🔥 {{ $row['streak'] }}</td>
                                <td class="py-2.5 pr-4">{{ $row['sessions'] }} sesi</td>
                                <td class="py-2.5 pr-4">{{ $row['avg_score'] }}</td>
                                <td class="py-2.5 text-ink-muted">{{ $row['last_activity']?->diffForHumans() ?? 'Belum ada' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-6 text-center text-ink-muted">Belum ada siswa terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
