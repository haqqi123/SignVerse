@extends('layouts.app')

@section('title', 'Progress Belajar - SignTeach')

@section('content')
<x-section-header title="Progress Belajar" subtitle="Pantau grafik perkembangan dan riwayat latihanmu 📈" />

{{-- 4 Stat Ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.6rem">
    <x-stat-card label="Total Latihan" :value="$stats['total_sessions']" />
    <x-stat-card label="Akurasi Rata-rata" :value="round($stats['avg_accuracy']) . '%'" />
    <x-stat-card label="Level" :value="$level['name']" :delta="number_format($xp) . ' XP'" />
    <x-stat-card label="Streak" :value="$streak['streak'] . ' hari 🔥'" />
</div>

{{-- 1. Akurasi per Kategori --}}
<div class="card" style="margin-bottom:1.6rem">
    <x-section-mini title="Akurasi per Kategori Materi" subtitle="Tingkat penguasaanmu pada masing-masing topik" />
    @if (!empty($categoryAccuracy))
        <div style="display:grid;gap:1rem;margin-top:0.8rem">
            @foreach ($categoryAccuracy as $cat => $data)
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.3rem">
                        <span style="font-weight:700;font-size:0.95rem">{{ ucfirst($cat) }}</span>
                        <span style="font-weight:700;color:var(--primary)">{{ $data['accuracy'] }}% ({{ $data['count'] }} sesi)</span>
                    </div>
                    <x-xp-bar :pct="$data['accuracy']" :height="8" />
                </div>
            @endforeach
        </div>
    @else
        <div class="caption">Belum ada data latihan untuk kategori materi.</div>
    @endif
</div>

{{-- 2. Tren Akurasi Harian (7 Hari Terakhir) --}}
<div class="card" style="margin-bottom:1.6rem">
    <x-section-mini title="Tren Akurasi (7 Hari Terakhir)" subtitle="Grafik perkembangan rata-rata harian" />
    @if (!empty($trend))
        <div style="display:flex;align-items:flex-end;gap:1.5rem;height:160px;padding:1.5rem 0.5rem 0.5rem 0.5rem;border-bottom:1px solid var(--card-border)">
            @foreach ($trend as $t)
                @php
                    $h = max(15, (int) ($t['accuracy'] * 1.3));
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end">
                    <span style="font-size:0.75rem;font-weight:700;color:var(--primary);margin-bottom:0.2rem">{{ $t['accuracy'] }}%</span>
                    <div style="width:100%;max-width:40px;height:{{ $h }}px;background:linear-gradient(180deg, var(--primary), var(--secondary));border-radius:6px 6px 0 0"></div>
                    <span class="caption" style="font-size:0.75rem;margin-top:0.4rem">{{ substr($t['day'], 5) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="caption">Belum ada cukup riwayat sesi latihan dalam 7 hari terakhir.</div>
    @endif
</div>

{{-- 3. Analisis Gestur yang Butuh Perhatian --}}
@if (!empty($gestureErrors))
    <div class="card" style="margin-bottom:1.6rem">
        <x-section-mini title="Analisis Kesalahan Gestur" subtitle="Huruf dengan frekuensi kesalahan tertinggi yang perlu dilatih ulang" />
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:0.8rem;margin-top:0.8rem">
            @foreach ($gestureErrors as $err)
                <div class="stat-card" style="padding:0.8rem;text-align:center">
                    <div style="font-size:1.8rem;font-weight:800;color:#dc2626">{{ $err['expected'] }}</div>
                    <div class="caption" style="font-size:0.8rem;margin-top:0.2rem">Akurasi: {{ $err['accuracy'] }}%</div>
                    <div class="caption" style="font-size:0.75rem;color:#b91c1c">{{ $err['wrong'] }}x salah dari {{ $err['n'] }} percobaan</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- 4. Tabel Riwayat Latihan --}}
<div class="card">
    <x-section-mini title="Riwayat Latihan Terakhir" subtitle="Daftar 25 sesi latihan terbarumu" />
    @if ($history->isNotEmpty())
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
                        <th>XP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($history as $h)
                        <tr>
                            <td class="caption">{{ substr($h->created_at, 0, 16) }}</td>
                            <td><b>{{ $h->material_title ?? '-' }}</b></td>
                            <td>{{ $h->lesson_title ?? $h->target }}</td>
                            <td style="font-weight:700;color:{{ $h->accuracy >= 80 ? '#059669' : '#d97706' }}">{{ round($h->accuracy) }}%</td>
                            <td>{{ round($h->final_score) }}</td>
                            <td>
                                @php
                                    $variant = match($h->grade) {
                                        'A' => 'teal',
                                        'B' => 'indigo',
                                        'C' => 'amber',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-badge :variant="$variant">{{ $h->grade }}</x-badge>
                            </td>
                            <td style="font-weight:600;color:var(--primary)">+{{ $h->xp_earned }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="caption" style="text-align:center;padding:1.4rem 0">
            Belum ada riwayat latihan. Buka halaman Materi Belajar untuk memulai latihan!
        </div>
    @endif
</div>
@endsection
