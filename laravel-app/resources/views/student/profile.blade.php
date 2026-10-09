@extends('layouts.app')

@section('title', 'Profil Siswa - SignTeach')

@section('content')
<x-section-header title="Profil Saya" subtitle="Kelola informasi akun dan tinjau progres aktivitas belajarmu 👤" />

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.6rem">
    {{-- Kolom Kiri: Kartu Profil & Ringkasan Cepat --}}
    <div>
        <div class="card" style="text-align:center;padding:1.8rem;margin-bottom:1.4rem">
            <div style="font-size:3.5rem;margin-bottom:0.5rem">👤</div>
            <div style="font-weight:800;font-size:1.3rem">{{ $user->name }}</div>
            <div class="caption">{{ $user->username }}</div>
            <div style="margin-top:0.8rem">
                <x-badge variant="indigo">Siswa SignTeach</x-badge>
            </div>
            <div class="caption" style="margin-top:0.8rem;font-size:0.8rem">
                Bergabung sejak: {{ substr($user->created_at ?? now(), 0, 10) }}
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem">
            <x-stat-card label="Total Sesi" :value="$stats['total_sessions']" />
            <x-stat-card label="Akurasi" :value="round($stats['avg_accuracy']) . '%'" />
            <x-stat-card label="Streak" :value="$streak['streak'] . ' hari'" />
            <x-stat-card label="Materi Selesai" :value="$stats['materials_done']" />
        </div>
    </div>

    {{-- Kolom Kanan: Gamifikasi & Form Pengaturan Akun --}}
    <div>
        <div class="card" style="margin-bottom:1.6rem">
            <div style="font-weight:800;font-size:1.1rem;margin-bottom:0.4rem;color:{{ $level['color'] }}">
                Tingkatan: {{ $level['name'] }}
            </div>
            <div class="caption" style="margin-bottom:0.6rem">
                Total Akumulasi XP: <b>{{ number_format($xp) }} XP</b>
            </div>
            <x-xp-bar :pct="$level['pct']" :height="10" />
            <div class="caption" style="margin-top:0.5rem">
                @if ($level['next_xp'] !== null)
                    Butuh <b>{{ number_format($level['next_xp'] - $xp) }} XP</b> lagi untuk naik ke tingkat berikutnya.
                @else
                    Selamat! Kamu telah mencapai tingkat kejuaraan tertinggi (Inclusive Champion) 🎉
                @endif
            </div>
        </div>

        <div class="card">
            <x-section-mini title="Pengaturan Profil" subtitle="Perbarui nama akun atau ganti kata sandi" />

            <form method="POST" action="{{ route('student.profile.update') }}" style="margin-top:1.2rem">
                @csrf
                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label for="username">Username / Email</label>
                    <input type="text" id="username" value="{{ $user->username }}" disabled style="background:#f5f5fa;cursor:not-allowed">
                    <div class="caption" style="margin-top:0.2rem">Username/email tidak dapat diubah.</div>
                </div>

                <div class="divider"></div>

                <div class="field">
                    <label for="password">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter">
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru">
                </div>

                <div style="margin-top:1.4rem">
                    <button type="submit" class="btn">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
