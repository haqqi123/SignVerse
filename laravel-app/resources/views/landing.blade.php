@extends('layouts.app')

@section('title', 'SignTeach - Belajar Bahasa Isyarat')

@section('content')
{{-- ── Hero ── --}}
<div class="hero">
    <div style="font-size:0.8rem;letter-spacing:0.35em;color:var(--muted);font-weight:700">
        SIGNTEACH &middot; BISINDO &middot; AI-POWERED
    </div>
    <h1><span class="grad">SignTeach</span></h1>
    <div class="sub">Platform AI untuk Belajar Bahasa Isyarat Indonesia</div>
    <div style="margin-top:1.2rem">
        <span class="st-badge badge-indigo">SIBI</span>
        <span class="st-badge badge-teal">BISINDO</span>
        <span class="st-badge badge-amber">AI-Powered</span>
    </div>
    <div style="margin-top:1.6rem;display:flex;gap:0.6rem;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('login') }}" class="btn">Mulai Belajar</a>
        <a href="{{ route('practice.teaser') }}" class="btn btn-secondary">Coba AI Practice</a>
        <a href="{{ route('login') }}" class="btn btn-secondary">Masuk sebagai Guru</a>
    </div>
</div>

<div class="divider"></div>

{{-- ── Statistik (dari database) ── --}}
<x-section-header title="Statistik Platform" subtitle="Perkembangan data latihan" />
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
    <x-stat-card label="Siswa Terdaftar" value="{{ $stats['students'] }}" />
    <x-stat-card label="Latihan Minggu Ini" value="{{ $stats['weeklySessions'] }}" />
    <x-stat-card label="Materi Pembelajaran" value="{{ $stats['materials'] }}" />
</div>

<div class="divider"></div>

{{-- ── Fitur utama ── --}}
<x-section-header title="Fitur Utama" subtitle="Semua yang kamu butuhkan untuk fasih berbahasa isyarat" />
@php
    $features = [
        ['✋', 'AI Practice', 'Latihan isyarat dengan kamera, deteksi real-time, skor dan umpan balik.'],
        ['🏆', 'Gamification', 'XP, level, badge, dan streak untuk menjaga semangat belajar.'],
        ['👨‍🏫', 'Lengkap untuk Guru', 'Dashboard, monitoring, assignment, dan laporan analisis.'],
    ];
@endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
    @foreach ($features as [$icon, $title, $desc])
        <div class="card" style="text-align:center;padding:1.6rem">
            <div style="font-size:2rem">{{ $icon }}</div>
            <div style="font-weight:800;font-size:1.05rem;margin:0.4rem 0">{{ $title }}</div>
            <div style="color:var(--muted);font-size:0.9rem">{{ $desc }}</div>
        </div>
    @endforeach
</div>

<div class="divider"></div>

{{-- ── Cara kerja ── --}}
<x-section-header title="Cara Kerja Sistem" subtitle="Tiga langkah mudah" />
@php
    $steps = [
        ['Pilih Materi', 'Alfabet, angka, atau kosakata dasar — di SIBI maupun BISINDO.'],
        ['Latihan dengan AI', 'Kamera menangkap tanganmu dan AI mendeteksi isyarat secara real-time.'],
        ['Terima Nilai & Rekomendasi', 'Akurasi, speed, consistency, dan rekomendasi materi berikutnya.'],
    ];
@endphp
@foreach ($steps as $i => [$title, $desc])
    <div style="display:grid;grid-template-columns:60px 1fr;gap:0.8rem;margin-bottom:0.7rem">
        <div class="stat-card" style="text-align:center;font-weight:800;color:var(--primary)">{{ $i + 1 }}</div>
        <div class="card">
            <div style="font-weight:700">{{ $title }}</div>
            <div style="color:var(--muted)">{{ $desc }}</div>
        </div>
    </div>
@endforeach

<div class="divider"></div>

{{-- ── Testimoni ── --}}
<x-section-header title="Kata Mereka" subtitle="Pengguna SignTeach" />
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="card">
        "Siswa jauh lebih bersemangat karena umpan balik AI langsung."
        <div style="font-weight:700;margin-top:0.6rem">— Budi Santoso, Guru SDLBN</div>
    </div>
    <div class="card">
        "Belajar isyarat jadi tidak bosan, akurasi saya naik setiap minggu."
        <div style="font-weight:700;margin-top:0.6rem">— Rina Putri, Siswa</div>
    </div>
</div>

<div class="divider"></div>

{{-- ── CTA & footer ── --}}
<div class="card" style="text-align:center;padding:2.4rem 1.5rem">
    <div style="font-size:1.5rem;font-weight:800">Siap memulai perjalanan isyaratmu?</div>
    <div style="color:var(--muted);margin:0.5rem 0 1.2rem">Daftar dan mulai belajar hari ini.</div>
    <a href="{{ route('register') }}" class="btn">Mulai Belajar Sekarang</a>
</div>

<div style="text-align:center;color:var(--muted);font-size:0.8rem;margin-top:1.6rem">
    SignTeach © 2026 — Platform AI Pembelajaran Bahasa Isyarat Indonesia · SIBI · BISINDO
</div>
@endsection
