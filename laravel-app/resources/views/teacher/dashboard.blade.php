@extends('layouts.app')

@section('title', 'Dashboard Guru - SignTeach')

@section('content')
<x-section-header title="Dashboard Guru" subtitle="Selamat datang, {{ auth()->user()->name }} 👋 Tinjau perkembangan kelas dan aktivitas belajarmu hari ini." />

{{-- 4 Stat Kartu Ringkasan Kelas --}}
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.6rem">
    <x-stat-card label="Total Siswa" :value="$summary['students']" />
    <x-stat-card label="Rata-rata Skor Kelas" :value="round($summary['avg_score']) . '%'" />
    <x-stat-card label="Latihan Minggu Ini" :value="$summary['weekly_sessions']" />
    <x-stat-card label="Assignment Aktif" :value="$summary['active_assignments']" />
</div>

{{-- Visualisasi Aktivitas Latihan Mingguan --}}
<div class="card" style="margin-bottom:1.6rem">
    <x-section-mini title="Aktivitas Latihan Siswa (7 Hari Terakhir)" subtitle="Frekuensi sesi latihan mandiri yang dilakukan siswa per hari" />
    @if (!empty($weekly))
        <div style="display:flex;align-items:flex-end;gap:1.5rem;height:160px;padding:1.5rem 0.5rem 0.5rem 0.5rem;border-bottom:1px solid var(--card-border)">
            @php
                $maxCount = max(1, max(array_column($weekly, 'count')));
            @endphp
            @foreach ($weekly as $w)
                @php
                    $h = max(15, (int) (($w['count'] / $maxCount) * 110));
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end">
                    <span style="font-size:0.8rem;font-weight:800;color:var(--primary);margin-bottom:0.2rem">{{ $w['count'] }}</span>
                    <div style="width:100%;max-width:45px;height:{{ $h }}px;background:linear-gradient(180deg, var(--primary), var(--secondary));border-radius:6px 6px 0 0"></div>
                    <span class="caption" style="font-size:0.75rem;margin-top:0.4rem">{{ substr($w['day'], 5) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="caption">Belum ada aktivitas latihan siswa pada minggu ini.</div>
    @endif
</div>

{{-- Assignment Terbaru --}}
<div class="card" style="margin-bottom:1.6rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem">
        <x-section-mini title="Assignment Terbaru" subtitle="Daftar penugasan kelas yang sedang berjalan" />
        <a href="{{ route('teacher.assignments') }}" class="btn btn-secondary" style="font-size:0.85rem;padding:0.4rem 0.8rem">Kelola Semua Tugas →</a>
    </div>

    @if ($assignments->isNotEmpty())
        <div style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul Tugas</th>
                        <th>Modul Materi</th>
                        <th>Target Siswa</th>
                        <th>Penyelesaian</th>
                        <th>Tenggat Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assignments->take(5) as $a)
                        <tr>
                            <td><b>{{ $a->title }}</b></td>
                            <td>{{ $a->material_title ?? '-' }}</td>
                            <td>{{ $a->total_students }} siswa</td>
                            <td>
                                <x-badge variant="teal">{{ $a->done }}/{{ $a->total_students }} Selesai</x-badge>
                            </td>
                            <td class="caption">{{ $a->deadline ? substr($a->deadline, 0, 10) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="caption" style="text-align:center;padding:1.4rem 0">
            Belum ada tugas yang dibuat. Buat tugas baru untuk memandu pembelajaran siswa.
        </div>
    @endif
</div>

{{-- Aksi Cepat Guru --}}
<div class="card">
    <x-section-mini title="Aksi Cepat Pengelolaan Kelas" subtitle="Pintasan cepat ke menu penting guru" />
    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;margin-top:0.8rem">
        <a href="{{ route('teacher.students') }}" class="btn btn-secondary" style="text-align:center;padding:1rem">
            <div style="font-size:1.8rem;margin-bottom:0.3rem">👥</div>
            <div style="font-weight:700">Daftar Siswa</div>
            <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">Pantau seluruh akun siswa</div>
        </a>
        <a href="{{ route('teacher.assignments') }}" class="btn btn-secondary" style="text-align:center;padding:1rem">
            <div style="font-size:1.8rem;margin-bottom:0.3rem">📋</div>
            <div style="font-weight:700">Buat Assignment</div>
            <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">Tugaskan modul materi baru</div>
        </a>
        <a href="{{ route('teacher.reports') }}" class="btn btn-secondary" style="text-align:center;padding:1rem">
            <div style="font-size:1.8rem;margin-bottom:0.3rem">📊</div>
            <div style="font-weight:700">Laporan & Ekspor</div>
            <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">Unduh rekap nilai CSV</div>
        </a>
    </div>
</div>
@endsection
