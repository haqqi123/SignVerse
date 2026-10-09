@extends('layouts.app')

@section('title', 'Laporan & Report - SignTeach')

@section('content')
<x-section-header title="Laporan & Rekapitulasi Kelas" subtitle="Evaluasi capaian belajar siswa dan unduh data penilaian ke format CSV 📊" />

{{-- Tab Navigasi Laporan --}}
<div class="card" style="margin-bottom:1.6rem;padding:0.6rem 0.8rem">
    <div style="display:flex;gap:0.8rem">
        <a href="{{ route('teacher.reports') }}" class="btn {{ empty($selectedId) ? '' : 'btn-secondary' }}">
            📦 Laporan Seluruh Kelas
        </a>
        <a href="{{ route('teacher.reports', ['student_id' => $students->first()?->id ?? 1]) }}" class="btn {{ !empty($selectedId) ? '' : 'btn-secondary' }}">
            🧑‍🎓 Laporan Individual Siswa
        </a>
    </div>
</div>

@if (empty($selectedId))
    {{-- ══════════════════════════════════════════════════════════════════════
         TAB 1: REKAP KELAS (Class Report)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:1.6rem">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.8rem">
            <x-section-mini title="Rekapitulasi Nilai & Aktivitas Kelas" subtitle="Tinjauan menyeluruh hasil latihan seluruh siswa" />
            <a href="{{ route('teacher.reports.export') }}" class="btn" style="background:#059669">
                ⬇️ Export Class Report (CSV)
            </a>
        </div>

        @if (!empty($report))
            <div style="overflow-x:auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Akurasi Rata-rata</th>
                            <th>Skor Akhir</th>
                            <th>Total Sesi</th>
                            <th>Topik Selesai</th>
                            <th>Terakhir Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $r)
                            <tr>
                                <td><b>{{ $r['name'] }}</b></td>
                                <td style="font-weight:700;color:{{ $r['accuracy'] >= 80 ? '#059669' : '#d97706' }}">
                                    {{ round($r['accuracy']) }}%
                                </td>
                                <td style="font-weight:700">{{ round($r['score']) }}</td>
                                <td>{{ $r['sessions'] }} sesi</td>
                                <td>{{ $r['materials'] }} topik</td>
                                <td class="caption">{{ $r['last_activity'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state emoji="📊" message="Belum ada data nilai kelas yang dapat direkap." />
        @endif
    </div>

@else
    {{-- ══════════════════════════════════════════════════════════════════════
         TAB 2: INDIVIDUAL STUDENT REPORT
         ══════════════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:1.6rem;padding:0.9rem 1.2rem">
        <form method="GET" action="{{ route('teacher.reports') }}" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <label for="student_id" style="font-weight:700;font-size:0.9rem;margin-bottom:0">Pilih Siswa:</label>
            <select id="student_id" name="student_id" onchange="this.form.submit()" style="padding:0.5rem 0.9rem;border-radius:10px;border:1px solid var(--card-border);background:#fff;font-family:inherit;font-size:0.95rem;max-width:320px;flex:1">
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" {{ $selectedId == $s->id ? 'selected' : '' }}>
                        {{ $s->name }} ({{ $s->username }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary" style="padding:0.45rem 1rem;font-size:0.85rem">Pilih</button>
        </form>
    </div>

    @if ($detail)
        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;margin-bottom:1.6rem">
            <x-stat-card label="Akurasi Siswa" :value="round($detail['stats']['avg_accuracy']) . '%'" />
            <x-stat-card label="Total Sesi Latihan" :value="$detail['stats']['total_sessions']" />
            <x-stat-card label="Akumulasi XP" :value="number_format(\App\Services\GamificationService::totalXp($detail['id'])) . ' XP'" />
        </div>

        {{-- Akurasi Kategori --}}
        <div class="card" style="margin-bottom:1.6rem">
            <x-section-mini title="Progres per Kategori Materi" subtitle="Rata-rata akurasi siswa pada tiap kategori" />
            @if (!empty($detail['category_accuracy']))
                <div style="display:grid;gap:0.8rem;margin-top:0.8rem">
                    @foreach ($detail['category_accuracy'] as $name => $data)
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:0.2rem">
                                <span style="font-weight:700">{{ ucfirst($name) }}</span>
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

        {{-- Riwayat Sesi Latihan Siswa --}}
        <div class="card">
            <x-section-mini title="Riwayat Latihan Lengkap Siswa" subtitle="Daftar seluruh sesi latihan mandiri siswa" />
            @if ($detail['history']->isNotEmpty())
                <div style="overflow-x:auto;margin-top:0.8rem">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Materi</th>
                                <th>Target</th>
                                <th>Akurasi</th>
                                <th>Skor</th>
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
                                    <td><x-badge variant="indigo">{{ $h->grade }}</x-badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="caption" style="text-align:center;padding:1rem 0">Belum ada riwayat latihan.</div>
            @endif
        </div>
    @endif
@endif
@endsection
