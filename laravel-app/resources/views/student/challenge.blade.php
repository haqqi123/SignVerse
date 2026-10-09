@extends('layouts.app')

@section('title', 'Challenge Harian - SignTeach')

@section('content')
<x-section-header title="Challenge Harian" subtitle="Selesaikan tantangan dan raih tambahan XP setiap hari 🎯" />

{{-- Daftar Tantangan Hari Ini --}}
@if (count($challenges) > 0)
    <div style="display:grid;gap:1rem;margin-bottom:1.6rem">
        @foreach ($challenges as $ch)
            @php
                $target = max(1, (int) $ch->target);
                $cur = min($target, (int) $ch->progress);
                $pct = (int) ($cur / $target * 100);
            @endphp
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.6rem">
                    <div>
                        <div style="font-weight:800;font-size:1.15rem;display:flex;align-items:center;gap:0.4rem">
                            {{ $ch->title }}
                            @if ($ch->completed)
                                <span style="color:#059669">✅ Selesai!</span>
                            @endif
                        </div>
                        <div class="caption" style="margin-top:0.2rem">{{ $ch->description }}</div>
                        <div class="caption" style="margin-top:0.5rem;font-weight:600">
                            Progres: {{ $cur }}/{{ $target }} selesai · Target Kategori: {{ ucfirst($ch->category) }}
                        </div>
                    </div>
                    <div style="text-align:right">
                        <x-badge variant="amber">+{{ $ch->reward_xp }} XP</x-badge>
                    </div>
                </div>

                <div style="margin-top:0.8rem">
                    <x-xp-bar :pct="$pct" :height="8" />
                </div>

                @if ($ch->completed)
                    <div style="color:var(--secondary);font-weight:700;font-size:0.85rem;margin-top:0.6rem">
                        Bonus +{{ $ch->reward_xp }} XP telah masuk ke akunmu! 🎉
                    </div>
                @else
                    <div style="margin-top:0.8rem">
                        <a href="{{ route('student.materials', ['category' => $ch->category]) }}" class="btn" style="font-size:0.85rem;padding:0.4rem 1rem">
                            Kerjakan Tantangan Ini 🔥
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <x-empty-state emoji="🎯" message="Tidak ada challenge aktif untuk hari ini. Kembali lagi besok!" />
@endif

{{-- Streak Belajar --}}
<div class="card" style="text-align:center;padding:1.6rem;margin-bottom:1.6rem">
    <div style="font-weight:800;color:var(--muted);font-size:0.9rem">Streak Kamu Saat Ini</div>
    <div style="font-size:2.8rem;font-weight:800;color:var(--text);margin:0.2rem 0">
        {{ $streak['streak'] }} 🔥 hari
    </div>
    <div class="caption">
        @if ($streak['is_today'])
            Kamu sudah berlatih hari ini! Terus jaga konsistensimu.
        @else
            Latihan hari ini untuk mempertahankan dan menambah streak belajarmu!
        @endif
    </div>
</div>

{{-- Riwayat Reward Challenge --}}
<x-section-mini title="Reward yang Sudah Diraih" subtitle="Daftar challenge harian yang berhasil kamu selesaikan" />
<div class="card">
    @forelse ($rewards as $r)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.7rem 0;border-bottom:1px solid var(--card-border)">
            <div style="display:flex;align-items:center;gap:0.6rem">
                <x-badge variant="indigo">+{{ $r->reward_xp }} XP</x-badge>
                <span style="font-weight:700;font-size:0.95rem">{{ $r->title }}</span>
            </div>
            <div class="caption" style="font-size:0.8rem">
                {{ substr($r->completed_at ?? '', 0, 16) }}
            </div>
        </div>
    @empty
        <div class="caption" style="text-align:center;padding:1rem 0">
            Selesaikan challenge harian untuk mulai mengumpulkan riwayat reward XP!
        </div>
    @endforelse
</div>
@endsection
