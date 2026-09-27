@extends('layouts.app')

@section('title', 'Monitoring Siswa - SignTeach')

@section('content')
<x-section-header title="Monitoring Siswa" subtitle="Pantau performa individu, akurasi per kategori, dan analisis kesalahan gestur siswa 🧑‍🎓" />

{{-- Pemilih Siswa --}}
<div class="card" style="margin-bottom:1.6rem;padding:0.9rem 1.2rem">
    <form method="GET" action="{{ route('teacher.monitoring') }}" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
        <label for="student_id" style="font-weight:700;font-size:0.9rem;margin-bottom:0">Pilih Siswa:</label>
        <select id="student_id" name="student_id" onchange="this.form.submit()" style="padding:0.5rem 0.9rem;border-radius:10px;border:1px solid var(--card-border);font-family:inherit;font-size:0.95rem;background:#fff;color:var(--text);flex:1;max-width:320px">
            @foreach ($students as $s)
                <option value="{{ $s->id }}" {{ $selectedId == $s->id ? 'selected' : '' }}>
                    {{ $s->name }} ({{ $s->username }})
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary" style="padding:0.45rem 1rem;font-size:0.85rem">Tampilkan</button>
    </form>
</div>

@if ($detail)
    {{-- Header Profil Singkat Siswa --}}
    <div class="card" style="margin-bottom:1.4rem;padding:1.2rem 1.4rem">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.8rem">
            <div>
                <div style="font-size:1.4rem;font-weight:800">{{ $detail['name'] }}</div>
                <div class="caption">{{ $detail['username'] }} · Terakhir aktif: {{ $detail['last_activity'] ?? '-' }}</div>
            </div>
            <x-badge variant="indigo">Siswa Terdaftar</x-badge>
        </div>
    </div>

    {{-- 4 Stat Kartu Siswa --}}
    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.6rem">
        <x-stat-card label="Total Sesi Latihan" :value="$detail['stats']['total_sessions']" />
        <x-stat-card label="Akurasi Rata-rata" :value="round($detail['stats']['avg_accuracy']) . '%'" />
        <x-stat-card label="Akumulasi XP" :value="number_format(\App\Services\GamificationService::totalXp($detail['id'])) . ' XP'" />
        <x-stat-card label="Streak Latihan" :value="\App\Services\GamificationService::streakInfo($detail['id'])['streak'] . ' hari 🔥'" />
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.6rem;margin-bottom:1.6rem">
        {{-- Sisi Kiri: Akurasi per Kategori --}}
        <div class="card">
            <x-section-mini title="Akurasi per Kategori Materi" subtitle="Tingkat keberhasilan latihan siswa per topik" />
            @if (!empty($detail['category_accuracy']))
                <div style="display:grid;gap:0.9rem;margin-top:0.8rem">
                    @foreach ($detail['category_accuracy'] as $cat => $data)
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:0.3rem">
                                <span style="font-weight:700;font-size:0.9rem">{{ ucfirst($cat) }}</span>
                                <span style="font-weight:700;color:var(--primary)">{{ $data['accuracy'] }}% ({{ $data['count'] }} sesi)</span>
                            </div>
                            <x-xp-bar :pct="$data['accuracy']" :height="8" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="caption">Belum ada data latihan untuk materi.</div>
            @endif
        </div>

        {{-- Sisi Kanan: Analisis Kesalahan Gestur --}}
        <div class="card">
            <x-section-mini title="Analisis Kesalahan Gestur" subtitle="Huruf yang paling sering salah dipraktekkan oleh siswa" />
            @if (!empty($detail['errors']))
                <div style="display:grid;gap:0.6rem;margin-top:0.8rem">
                    @foreach ($detail['errors'] as $e)
                        @php
                            $isGood = ($e['accuracy'] >= 85);
                        @endphp
                        <div class="stat-card" style="padding:0.6rem 0.9rem;display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <div style="font-weight:800;font-size:1.1rem;display:flex;align-items:center;gap:0.4rem">
                                    <span>{{ $isGood ? '✅' : '⚠️' }}</span>
                                    <span>Gestur "{{ $e['expected'] }}"</span>
                                </div>
                                <div class="caption" style="font-size:0.75rem;margin-top:0.1rem">
                                    {{ $e['wrong'] }}x salah dari {{ $e['n'] }} percobaan
                                </div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-weight:800;font-size:1.1rem;color:{{ $isGood ? '#059669' : '#dc2626' }}">{{ round($e['accuracy']) }}%</div>
                                <x-badge :variant="$isGood ? 'teal' : 'amber'">{{ $isGood ? 'Baik' : 'Perlu Latihan' }}</x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="caption">Belum ada data kesalahan gestur pada riwayat siswa ini.</div>
            @endif
        </div>
    </div>

    {{-- Riwayat Latihan 10 Terakhir --}}
    <div class="card" style="margin-bottom:1.6rem">
        <x-section-mini title="Riwayat Sesi Latihan (10 Terakhir)" subtitle="Daftar latihan mandiri terbaru yang diselesaikan siswa" />
        @if ($detail['history']->isNotEmpty())
            <div style="overflow-x:auto;margin-top:0.8rem">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Materi</th>
                            <th>Target</th>
                            <th>Akurasi</th>
                            <th>Skor Akhir</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detail['history'] as $h)
                            <tr>
                                <td class="caption">{{ substr($h->created_at, 0, 16) }}</td>
                                <td><b>{{ $h->material_title ?? '-' }}</b></td>
                                <td>{{ $h->lesson_title ?? $h->target }}</td>
                                <td style="font-weight:700;color:{{ $h->accuracy >= 80 ? '#059669' : '#d97706' }}">{{ round($h->accuracy) }}%</td>
                                <td>{{ round($h->final_score) }}</td>
                                <td>
                                    <x-badge variant="indigo">{{ $h->grade }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="caption" style="text-align:center;padding:1rem 0">Belum ada riwayat sesi latihan.</div>
        @endif
    </div>

    {{-- Achievement Siswa --}}
    <div class="card">
        <x-section-mini title="Pencapaian Badge Siswa" subtitle="Lencana yang telah berhasil diraih" />
        @php
            $unlockedBadges = array_filter($detail['achievements'], fn($b) => $b['unlocked']);
        @endphp
        @if (!empty($unlockedBadges))
            <div style="display:flex;flex-wrap:wrap;gap:0.8rem;margin-top:0.8rem">
                @foreach ($unlockedBadges as $b)
                    <div class="stat-card" style="padding:0.7rem 1rem;text-align:center;min-width:120px">
                        <div style="font-size:1.8rem">🏆</div>
                        <div style="font-weight:800;font-size:0.9rem;margin-top:0.2rem">{{ $b['name'] }}</div>
                        <div class="caption" style="font-size:0.75rem">{{ substr($b['unlocked_at'] ?? '', 0, 10) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="caption" style="text-align:center;padding:1rem 0">Siswa ini belum memiliki badge yang terbuka.</div>
        @endif
    </div>
@else
    <x-empty-state emoji="🧑‍🎓" message="Pilih siswa untuk melihat analitik dan hasil latihan mendalam." />
@endif
@endsection
