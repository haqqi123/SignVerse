@extends('layouts.app')

@section('title', 'Profil Guru - SignTeach')

@section('content')
<x-section-header title="Profil Guru" subtitle="Informasi akun pengajar dan pengaturan kelas 🧑‍🏫" />

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.6rem">
    {{-- Kolom Kiri: Kartu Profil --}}
    <div>
        <div class="card" style="text-align:center;padding:1.8rem;margin-bottom:1.4rem">
            <div style="font-size:3.5rem;margin-bottom:0.5rem">🧑‍🏫</div>
            <div style="font-weight:800;font-size:1.3rem">{{ $user->name }}</div>
            <div class="caption">{{ $user->username }}</div>
            <div style="margin-top:0.8rem">
                <x-badge variant="teal">Tenaga Pengajar (Guru)</x-badge>
            </div>
            <div class="caption" style="margin-top:0.8rem;font-size:0.8rem">
                Bergabung sejak: {{ substr($user->created_at ?? now(), 0, 10) }}
            </div>
        </div>

        <div class="card">
            <div style="font-weight:800;font-size:0.95rem;margin-bottom:0.4rem">💡 Panduan Guru</div>
            <div class="caption" style="line-height:1.4">
                1. <b>Siswa:</b> Tinjau status keaktifan kelas.<br>
                2. <b>Monitoring:</b> Evaluasi akurasi dan gestur sulit per siswa.<br>
                3. <b>Assignment:</b> Berikan tugas modul materi.<br>
                4. <b>Laporan:</b> Unduh rekap nilai berkala (CSV).
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Pengaturan Akun & Kata Sandi --}}
    <div>
        <div class="card">
            <x-section-mini title="Pengaturan Profil Pengajar" subtitle="Perbarui nama akun atau ganti kata sandi login" />

            <form method="POST" action="{{ route('teacher.profile.update') }}" style="margin-top:1.2rem">
                @csrf
                <div class="field">
                    <label for="name">Nama Lengkap Guru</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label for="username">Username / Email</label>
                    <input type="text" id="username" value="{{ $user->username }}" disabled style="background:#f5f5fa;cursor:not-allowed">
                    <div class="caption" style="margin-top:0.2rem">Username pengajar tidak dapat diubah.</div>
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
                    <button type="submit" class="btn">Simpan Perubahan Profil</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
