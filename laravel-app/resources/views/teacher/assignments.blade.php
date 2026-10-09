@extends('layouts.app')

@section('title', 'Manajemen Assignment - SignTeach')

@section('content')
<x-section-header title="Manajemen Assignment" subtitle="Kelola dan berikan tugas latihan modul materi kepada siswa kelas 📋" />

{{-- Form Pembuatan Assignment Baru --}}
<div class="card" style="margin-bottom:2rem">
    <x-section-mini title="➕ Buat Assignment Baru" subtitle="Tugaskan modul materi kepada satu atau beberapa siswa sekaligus" />

    <form method="POST" action="{{ route('teacher.assignments.create') }}" style="margin-top:1.2rem">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem">
            <div class="field">
                <label for="material_id">Pilih Modul Materi Target</label>
                <select id="material_id" name="material_id" required style="width:100%;padding:0.6rem 0.9rem;border-radius:10px;border:1px solid var(--card-border);background:#fff;font-family:inherit;font-size:0.95rem">
                    @foreach ($materials as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->title }} ({{ $m->sign_system }} · {{ ucfirst($m->category) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="title">Judul Tugas</label>
                <input type="text" id="title" name="title" placeholder="Contoh: Latihan Pengenalan Alfabet A-E" required>
            </div>
        </div>

        <div class="field">
            <label for="description">Deskripsi & Instruksi Tugas</label>
            <input type="text" id="description" name="description" placeholder="Instruksi tambahan bagi siswa untuk menyelesaikan tugas...">
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.2rem">
            <div class="field">
                <label>Pilih Siswa Penerima Tugas (Minimal 1)</label>
                <div style="max-height:160px;overflow-y:auto;border:1px solid var(--card-border);border-radius:10px;padding:0.8rem;background:#fff;display:grid;grid-template-columns:1fr 1fr;gap:0.6rem">
                    @foreach ($students as $s)
                        <label style="display:flex;align-items:center;gap:0.5rem;font-weight:400;font-size:0.9rem;cursor:pointer">
                            <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" checked>
                            <span>{{ $s->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="deadline">Tenggat Waktu (Deadline)</label>
                <input type="date" id="deadline" name="deadline" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
            </div>
        </div>

        <div style="margin-top:1rem">
            <button type="submit" class="btn">Buat & Bagikan Tugas 🚀</button>
        </div>
    </form>
</div>

{{-- Daftar Assignment yang Sudah Dibuat --}}
<x-section-mini title="Daftar Assignment Kelas" subtitle="Status pengerjaan dan kemajuan tugas oleh siswa" />
@if ($assignments->isNotEmpty())
    <div style="display:grid;gap:1.2rem;margin-top:0.8rem">
        @foreach ($assignments as $a)
            @php
                $pct = $a->total_students > 0 ? (int) ($a->done / $a->total_students * 100) : 0;
            @endphp
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.8rem">
                    <div>
                        <div style="display:flex;align-items:center;gap:0.6rem">
                            <span style="font-weight:800;font-size:1.2rem">{{ $a->title }}</span>
                            <x-badge variant="teal">{{ $a->done }}/{{ $a->total_students }} Siswa Selesai</x-badge>
                        </div>
                        <div class="caption" style="margin-top:0.3rem">{{ $a->description }}</div>
                        <div class="caption" style="margin-top:0.5rem;font-weight:600">
                            Modul: <b>{{ $a->material_title ?? '-' }}</b> · Tenggat Waktu: {{ $a->deadline ? substr($a->deadline, 0, 10) : '-' }}
                        </div>
                    </div>
                </div>

                <div style="margin-top:0.8rem">
                    <x-xp-bar :pct="$pct" :height="8" />
                </div>

                {{-- Detail Status Per Siswa --}}
                @php
                    $items = \App\Services\AssignmentService::assignmentStudents($a->id);
                @endphp
                <div style="margin-top:1rem;padding-top:0.8rem;border-top:1px solid var(--card-border)">
                    <div class="caption" style="font-weight:700;margin-bottom:0.4rem">Rincian Siswa Penerima:</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:0.6rem">
                        @foreach ($items as $it)
                            <div class="stat-card" style="padding:0.5rem 0.8rem;display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <div style="font-weight:700;font-size:0.85rem">{{ $it->student_name }}</div>
                                    <div class="caption" style="font-size:0.75rem">{{ $it->completed_at ? substr($it->completed_at, 0, 10) : '-' }}</div>
                                </div>
                                <x-status-pill :status="$it->status" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <x-empty-state emoji="📋" message="Belum ada assignment yang dibuat. Buat tugas baru melalui formulir di atas." />
@endif
@endsection
