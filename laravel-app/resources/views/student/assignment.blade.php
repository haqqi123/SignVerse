@extends('layouts.app')

@section('title', 'Assignment Siswa - SignTeach')

@section('content')
<x-section-header title="Assignment" subtitle="Daftar tugas pembelajaran yang diberikan oleh gurumu 📝" />

{{-- Ringkasan Status Penugasan --}}
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.6rem">
    <x-stat-card label="Total Tugas" :value="$counts['total']" />
    <x-stat-card label="Belum Dimulai" :value="$counts['belum_dimulai']" />
    <x-stat-card label="Sedang Dikerjakan" :value="$counts['sedang_dikerjakan']" />
    <x-stat-card label="Selesai" :value="$counts['selesai']" />
</div>

{{-- Daftar Tugas --}}
@forelse ($assignments as $a)
    @php
        $today = date('Y-m-d');
        $isLate = ($a->deadline && $a->status !== 'selesai' && $a->deadline < $today);
    @endphp
    <div class="card" style="margin-bottom:1.2rem">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.8rem">
            <div>
                <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap">
                    <span style="font-weight:800;font-size:1.2rem;color:var(--text)">{{ $a->title }}</span>
                    <x-status-pill :status="$a->status" />
                    @if ($isLate)
                        <span class="st-badge badge-amber" style="background:#fee2e2;color:#b91c1c">⚠️ Melewati Deadline</span>
                    @endif
                </div>

                <div class="caption" style="margin-top:0.4rem;font-size:0.9rem">{{ $a->description }}</div>

                <div class="caption" style="margin-top:0.6rem;font-weight:600">
                    Modul Materi: <b>{{ $a->material_title ?? '-' }}</b>
                    @if ($a->material_category)
                        <x-badge variant="indigo">{{ ucfirst($a->material_category) }}</x-badge>
                    @endif
                    · Tenggat Waktu: <span style="color:{{ $isLate ? '#b91c1c' : 'inherit' }}">{{ $a->deadline ? substr($a->deadline, 0, 10) : 'Tanpa batas' }}</span>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div style="display:flex;gap:0.6rem;align-items:center">
                @if ($a->status === 'belum dimulai')
                    <form method="POST" action="{{ route('student.assignment.start', $a->assignment_id) }}">
                        @csrf
                        <button type="submit" class="btn">
                            ▶ Mulai Kerjakan
                        </button>
                    </form>
                @elseif ($a->status === 'sedang dikerjakan')
                    <form method="POST" action="{{ route('student.assignment.start', $a->assignment_id) }}">
                        @csrf
                        <button type="submit" class="btn">
                            ▶ Lanjutkan Latihan
                        </button>
                    </form>
                    <form method="POST" action="{{ route('student.assignment.complete', $a->assignment_id) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="border:1px solid #10b981;color:#059669">
                            ✔ Submit Selesai
                        </button>
                    </form>
                @else
                    <div style="color:#059669;font-weight:700;font-size:0.9rem;display:flex;align-items:center;gap:0.4rem">
                        <span>✅ Selesai</span>
                        @if ($a->completed_at)
                            <span class="caption" style="font-size:0.75rem">({{ substr($a->completed_at, 0, 10) }})</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <x-empty-state emoji="📝" message="Belum ada tugas dari gurumu saat ini. Santai sejenak atau lanjutkan latihan mandiri!" />
@endforelse

<div class="card" style="margin-top:2rem;background:#fbfaff">
    <div class="caption">
        💡 <b>Alur Otomatis Penugasan:</b> Saat kamu mengklik "Mulai Kerjakan", kamu akan langsung diarahkan ke modul latihan materi tersebut. Setelah sesi latihan selesai, status tugas akan <b>otomatis menjadi Selesai</b> tanpa harus submit manual!
    </div>
</div>
@endsection
