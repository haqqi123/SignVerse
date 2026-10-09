@extends('layouts.app')

@section('title', 'Achievement Siswa - SignTeach')

@section('content')
@php
    $badgeIcons = [
        'first_practice' => '🚀',
        'master_alfabet' => '🔤',
        'master_angka' => '🔢',
        'sibi_explorer' => '📘',
        'bisindo_explorer' => '📙',
        'streak_7' => '🔥',
        'challenger' => '🎯',
        'perfect_round' => '💯',
    ];
@endphp

<x-section-header title="Achievement" subtitle="Kumpulkan semua badge dan raih prestasi belajarmu! 🏆" />

{{-- Ringkasan Statistik --}}
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1rem">
    <x-stat-card label="Badge Terbuka" :value="count($unlocked) . '/' . count($badges)" />
    <x-stat-card label="Total XP" :value="number_format($xp)" />
    <x-stat-card label="Level" :value="$level['name']" />
    <x-stat-card label="Streak" :value="$streak['streak'] . ' hari 🔥'" />
</div>

<div class="card" style="margin-bottom:1.8rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem">
        <div style="font-weight:700;font-size:0.9rem;color:{{ $level['color'] }}">Level: {{ $level['name'] }}</div>
        <div class="caption">{{ number_format($xp) }} XP</div>
    </div>
    <x-xp-bar :pct="$level['pct']" :height="8" />
</div>

{{-- 1. Badge Terbuka (Terbaru di atas) --}}
@if (count($unlocked) > 0)
    <x-section-mini title="Diraih" :subtitle="count($unlocked) . ' badge berhasil kamu buka'" />
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:1rem;margin-bottom:1.8rem">
        @foreach ($unlocked as $b)
            @php
                $icon = $badgeIcons[$b['key']] ?? '🏆';
                $unlockedDate = substr($b['unlocked_at'] ?? '', 0, 10);
            @endphp
            <div class="stat-card" style="text-align:center;padding:1.4rem">
                <div style="font-size:2.4rem;margin-bottom:0.2rem">{{ $icon }}</div>
                <div style="font-weight:800;font-size:1.1rem;color:var(--text)">{{ $b['name'] }}</div>
                <div class="caption" style="margin-top:0.2rem">{{ $b['description'] }}</div>
                <div style="color:var(--secondary);font-size:0.82rem;font-weight:700;margin-top:0.6rem">
                    ✓ Unlocked {{ $unlockedDate }}
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- 2. Badge Terkunci + Progress Menuju Target --}}
@if (count($locked) > 0)
    <x-section-mini title="Menuju Badge Berikutnya" subtitle="Lanjutkan latihan untuk membuka badge ini" />
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:1rem">
        @foreach ($locked as $b)
            @php
                $icon = $badgeIcons[$b['key']] ?? '🔒';
                $p = $progress[$b['key']] ?? null;
            @endphp
            <div class="stat-card" style="text-align:center;padding:1.4rem;opacity:0.85">
                <div style="font-size:2.2rem;margin-bottom:0.2rem;filter:grayscale(0.6)">{{ $icon }}</div>
                <div style="font-weight:800;font-size:1.05rem;color:var(--text)">{{ $b['name'] }}</div>
                <div class="caption" style="margin-top:0.2rem">{{ $b['description'] }}</div>
                <div class="caption" style="font-size:0.8rem;margin-top:0.4rem;font-weight:600">
                    🔒 Locked
                </div>
                @if ($p)
                    @php
                        $target = max(1, (int) $p['target']);
                        $cur = min($target, (int) $p['current']);
                        $pct = (int) ($cur / $target * 100);
                    @endphp
                    <div style="margin-top:0.6rem;text-align:left">
                        <x-xp-bar :pct="$pct" :height="6" />
                        <div class="caption" style="text-align:right;margin-top:0.2rem;font-size:0.75rem">
                            {{ $cur }}/{{ $target }}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

@if (count($badges) === 0)
    <x-empty-state emoji="🏆" message="Belum ada data badge yang tersedia." />
@endif
@endsection
