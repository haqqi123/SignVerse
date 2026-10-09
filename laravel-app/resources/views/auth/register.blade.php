@extends('layouts.app')

@section('title', 'Daftar - SignTeach')

@section('content')
<div style="max-width:420px;margin:2rem auto">
    <div class="section-title">Daftar Akun Baru</div>
    <div class="section-sub">Mulai belajar Bahasa Isyarat Indonesia hari ini</div>

    <div class="card">
        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="field">
                <label for="name">Nama lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Kamu" required autofocus>
                @error('name') <div class="alert alert-error" style="margin-top:0.4rem">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="username">Email</label>
                <input type="email" id="username" name="username" value="{{ old('username') }}" placeholder="nama@example.com" required>
                @error('username') <div class="alert alert-error" style="margin-top:0.4rem">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                @error('password') <div class="alert alert-error" style="margin-top:0.4rem">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Konfirmasi password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn btn-full">Daftar Sekarang</button>
        </form>
    </div>

    <div style="text-align:center;margin-top:1rem">
        <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600">Sudah punya akun? Masuk</a>
    </div>
</div>
@endsection
