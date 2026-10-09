@extends('layouts.app')

@section('title', 'Dashboard Siswa - SignTeach')

@section('content')
@php
    $hour = (int) date('H');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="section-title">{{ $greeting }}, {{ $user->name }}! 👋</div>
<div class="section-sub">Selamat datang kembali di dashboard pembelajaran bahasa isyaratmu.</div>

{{-- 3 Kartu Utama & Level Bar --}}
<div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:1rem;margin-bottom:1rem">
    <x-stat-card label="Progress Belajar" :value="round($stats['avg_accuracy']) . '%'" :delta="$stats['total_sessions'] . ' sesi latihan'" />
    <x-stat-card label="Level" :value="$level['name']" />
    <x-stat-card label="Total XP" :value="number_format($xp) . ' XP'" />
</div>

<div class="card" style="margin-bottom:1.4rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem">
        <div style="font-weight:700;font-size:0.9rem;color:{{ $level['color'] }}">Level: {{ $level['name'] }}</div>
        <div class="caption">{{ number_format($xp) }} XP</div>
    </div>
    <x-xp-bar :pct="$level['pct']" :height="8" />
    <div class="caption" style="margin-top:0.4rem">
        @if ($level['next_xp'] !== null)
            {{ number_format($level['next_xp'] - $xp) }} XP lagi menuju level berikutnya.
        @else
            Level maksimum tercapai, hebat! 🎉
        @endif
    </div>
</div>

{{-- 4 Stat Grid --}}
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.4rem">
    <x-stat-card label="Total Latihan" :value="$stats['total_sessions']" />
    <x-stat-card label="Akurasi Rata-rata" :value="round($stats['avg_accuracy']) . '%'" />
    <x-stat-card label="Materi Dipelajari" :value="$stats['materials_done']" />
    <x-stat-card label="Streak Belajar" :value="$streak['streak'] . ' hari 🔥'" />
</div>

{{-- Konten 2 Kolom --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.4rem">
    {{-- Kolom Kiri: Lanjutkan Belajar & Rekomendasi AI --}}
    <div>
        <x-section-mini title="Lanjutkan Belajar" subtitle="Materi terakhir yang kamu pelajari" />
        @if ($last)
            <div class="card" style="margin-bottom:1.4rem">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <div class="caption" style="font-weight:600">
                            {{ $last->material_title ?? 'Materi' }} · {{ ucfirst($last->category ?? '') }}
                            <x-badge :variant="$last->sign_system === 'SIBI' ? 'indigo' : 'teal'">{{ $last->sign_system ?? 'SIBI' }}</x-badge>
                        </div>
                        <div style="font-size:1.4rem;font-weight:800;margin:0.3rem 0;color:var(--text)">
                            {{ $last->target ?? '' }}
                        </div>
                        <div class="caption">{{ $last->lesson_title ?? 'Latihan Terakhir' }}</div>
                    </div>
                    <a href="{{ route('student.practice', ['lesson_id' => $last->lesson_id]) }}" class="btn">Lanjutkan Latihan ✋</a>
                </div>
            </div>
        @else
            <div class="card" style="margin-bottom:1.4rem;text-align:center;padding:1.6rem">
                <div style="font-size:2.2rem;margin-bottom:0.4rem">🚀</div>
                <div style="font-weight:700">Belum ada aktivitas latihan</div>
                <div class="caption" style="margin-bottom:1rem">Mulai langkah pertamamu dengan mempelajari Alfabet SIBI!</div>
                <a href="{{ route('student.materials') }}" class="btn">Mulai Belajar Sekarang</a>
            </div>
        @endif

        <x-section-mini title="AI Learning Recommendation" subtitle="Jalur belajar yang dipersonalisasi untukmu" />
        <div class="card">
            <div style="display:flex;align-items:flex-start;gap:0.8rem">
                <div style="font-size:1.8rem">💡</div>
                <div>
                    <div style="font-weight:800;font-size:1.05rem">{{ $recommend['title'] }}</div>
                    <div class="caption" style="margin-top:0.3rem;line-height:1.4">{{ $recommend['text'] }}</div>
                    <div style="margin-top:0.8rem">
                        <a href="{{ route('student.materials', ['category' => $recommend['category'] ?? 'alfabet']) }}" class="btn btn-secondary" style="font-size:0.85rem;padding:0.4rem 0.9rem">
                            Lihat Modul {{ ucfirst($recommend['category'] ?? 'Alfabet') }} →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Tantangan Harian & Badge Terbaru --}}
    <div>
        <x-section-mini title="Daily Challenge" subtitle="Selesaikan untuk bonus XP" />
        <div class="card" style="margin-bottom:1.4rem">
            @forelse ($challenges as $ch)
                @php
                    $target = max(1, (int) $ch->target);
                    $cur = min($target, (int) $ch->progress);
                    $pct = (int) ($cur / $target * 100);
                @endphp
                <div style="margin-bottom:0.9rem;padding-bottom:0.9rem;border-bottom:1px solid var(--card-border)">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.2rem">
                        <div style="font-weight:700;font-size:0.95rem">
                            {{ $ch->title }}
                            @if ($ch->completed)
                                <span style="color:#059669">✅</span>
                            @endif
                        </div>
                        <x-badge variant="amber">+{{ $ch->reward_xp }} XP</x-badge>
                    </div>
                    <div class="caption" style="font-size:0.8rem;margin-bottom:0.4rem">
                        Progres: {{ $cur }}/{{ $target }}
                    </div>
                    <x-xp-bar :pct="$pct" :height="6" />
                </div>
            @empty
                <div class="caption" style="text-align:center;padding:1rem 0">Tidak ada challenge hari ini.</div>
            @endforelse
            <a href="{{ route('student.challenge') }}" class="btn btn-secondary btn-full" style="font-size:0.85rem;text-align:center">Buka Semua Tantangan</a>
        </div>

        <x-section-mini title="Achievement Terbaru" subtitle="Pencapaian belajarmu" />
        <div class="card" style="text-align:center;padding:1.4rem">
            @if ($newestBadge)
                <div style="font-size:2.4rem;margin-bottom:0.3rem">🏆</div>
                <div style="font-weight:800;font-size:1.1rem">{{ $newestBadge['name'] }}</div>
                <div class="caption" style="margin-top:0.3rem">{{ $newestBadge['description'] }}</div>
                <div style="color:var(--secondary);font-size:0.8rem;font-weight:600;margin-top:0.5rem">
                    ✓ Diraih {{ substr($newestBadge['unlocked_at'] ?? '', 0, 10) }}
                </div>
            @else
                <div style="font-size:2.2rem;margin-bottom:0.3rem">🎯</div>
                <div style="font-weight:700">Belum Ada Badge</div>
                <div class="caption" style="margin-top:0.3rem">Selesaikan sesi latihan pertamamu untuk membuka badge pertama!</div>
            @endif
            <div style="margin-top:1rem">
                <a href="{{ route('student.achievements') }}" class="btn btn-secondary btn-full" style="font-size:0.85rem">Lihat Semua Badge</a>
            </div>
        </div>
    </div>
</div>
@endsection
