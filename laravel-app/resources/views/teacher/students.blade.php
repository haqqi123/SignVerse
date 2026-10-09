@extends('layouts.app')

@section('title', 'Daftar Siswa - SignTeach')

@section('content')
<x-section-header title="Daftar Siswa" subtitle="Pantau seluruh siswa bimbingan dan performa belajarnya 👥" />

<div class="card">
    @if (!empty($report))
        <div style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Akurasi Rata-rata</th>
                        <th>Skor Akhir</th>
                        <th>Total Sesi</th>
                        <th>Materi Selesai</th>
                        <th>Terakhir Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report as $s)
                        <tr>
                            <td>
                                <b>{{ $s['name'] }}</b>
                            </td>
                            <td>
                                <span style="font-weight:700;color:{{ $s['accuracy'] >= 80 ? '#059669' : ($s['accuracy'] >= 60 ? '#d97706' : '#dc2626') }}">
                                    {{ round($s['accuracy']) }}%
                                </span>
                            </td>
                            <td style="font-weight:700">{{ round($s['score']) }}</td>
                            <td>{{ $s['sessions'] }} sesi</td>
                            <td>{{ $s['materials'] }} topik</td>
                            <td class="caption">{{ $s['last_activity'] ?? '-' }}</td>
                            <td>
                                <a href="{{ route('teacher.monitoring', ['student_id' => $s['id']]) }}" class="btn btn-secondary" style="padding:0.25rem 0.6rem;font-size:0.8rem">
                                    Lihat Detail →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-empty-state emoji="👥" message="Belum ada siswa yang terdaftar dalam sistem." />
    @endif
</div>
@endsection
