@extends('layouts.app')

@section('title', 'Materi Belajar - SignTeach')

@section('content')
<x-section-header title="Materi Belajar" subtitle="Pilih modul materi dan mulai tingkatkan kemampuan isyaratmu 📚" />

{{-- Filter Kategori & Sistem Isyarat --}}
<div class="card" style="margin-bottom:1.6rem;padding:0.9rem 1.2rem">
    <div style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:0.5rem">
            <span style="font-weight:700;font-size:0.85rem;color:var(--muted)">Kategori:</span>
            <a href="{{ route('student.materials', ['system' => $activeSystem]) }}"
               class="btn {{ empty($activeCategory) ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">Semua</a>
            <a href="{{ route('student.materials', ['category' => 'alfabet', 'system' => $activeSystem]) }}"
               class="btn {{ $activeCategory === 'alfabet' ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">🔤 Alfabet</a>
            <a href="{{ route('student.materials', ['category' => 'angka', 'system' => $activeSystem]) }}"
               class="btn {{ $activeCategory === 'angka' ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">🔢 Angka</a>
            <a href="{{ route('student.materials', ['category' => 'kosakata', 'system' => $activeSystem]) }}"
               class="btn {{ $activeCategory === 'kosakata' ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">🗣️ Kosakata</a>
        </div>

        <div style="display:flex;align-items:center;gap:0.5rem">
            <span style="font-weight:700;font-size:0.85rem;color:var(--muted)">Sistem:</span>
            <a href="{{ route('student.materials', ['category' => $activeCategory]) }}"
               class="btn {{ empty($activeSystem) ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">Semua</a>
            <a href="{{ route('student.materials', ['category' => $activeCategory, 'system' => 'SIBI']) }}"
               class="btn {{ $activeSystem === 'SIBI' ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">SIBI</a>
            <a href="{{ route('student.materials', ['category' => $activeCategory, 'system' => 'BISINDO']) }}"
               class="btn {{ $activeSystem === 'BISINDO' ? '' : 'btn-secondary' }}" style="padding:0.35rem 0.8rem;font-size:0.82rem">BISINDO</a>
        </div>
    </div>
</div>

{{-- Daftar Materi --}}
@forelse ($materials as $material)
    @php
        $prog = $progress[$material->id] ?? null;
        $lessons = $material->lessons ?? collect();
    @endphp
    <div class="card" style="margin-bottom:1.4rem">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.8rem">
            <div>
                <div style="display:flex;align-items:center;gap:0.5rem">
                    <span style="font-weight:800;font-size:1.25rem">{{ $material->title }}</span>
                    <x-badge :variant="$material->sign_system === 'SIBI' ? 'indigo' : 'teal'">{{ $material->sign_system }}</x-badge>
                    <x-badge variant="amber">{{ ucfirst($material->category) }}</x-badge>
                </div>
                <div class="caption" style="margin-top:0.3rem">{{ $material->description }}</div>
                <div class="caption" style="margin-top:0.3rem;font-weight:600">
                    {{ count($lessons) }} materi latihan
                    @if ($prog)
                        · <span style="color:#059669">{{ $prog['sessions_count'] }}x dilatih (Akurasi rata-rata: {{ $prog['avg_accuracy'] }}%)</span>
                    @endif
                </div>
            </div>
            @if ($lessons->isNotEmpty())
                <a href="{{ route('student.practice', ['lesson_id' => $lessons->first()->id]) }}" class="btn">
                    Mulai Belajar ✋
                </a>
            @endif
        </div>

        {{-- Daftar Latihan Lesson --}}
        @if ($lessons->isNotEmpty())
            <div style="margin-top:1.2rem;border-top:1px solid var(--card-border);padding-top:1rem">
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:0.8rem">
                    @foreach ($lessons as $lesson)
                        @php
                            $target = $lesson->practice_target ?: $lesson->target;
                        @endphp
                        <div class="stat-card" style="text-align:center;padding:0.8rem">
                            <div style="font-weight:800;font-size:1.3rem;color:var(--primary)">{{ $target }}</div>
                            <div class="caption" style="font-size:0.75rem;margin-top:0.2rem">{{ $lesson->title }}</div>
                            <div style="margin-top:0.6rem">
                                <a href="{{ route('student.practice', ['lesson_id' => $lesson->id]) }}"
                                   class="btn btn-secondary" style="padding:0.25rem 0.6rem;font-size:0.75rem;width:100%">
                                    Latih
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@empty
    <x-empty-state emoji="📚" message="Tidak ada materi yang sesuai dengan filter yang dipilih." />
@endforelse

<div class="card" style="margin-top:2rem;background:#fbfaff">
    <div class="caption">
        💡 <b>Catatan Deteksi AI:</b> Model deteksi mengenali isyarat alfabet (fingerspelling) dari sistem SIBI. Kata dibangun dengan mengeja huruf demi huruf (misal: SAYA = S-A-Y-A).
    </div>
</div>
@endsection
