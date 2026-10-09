@extends('layouts.app')

@section('title', 'Masuk ke Akun - SignTeach')
@section('no_sidebar', true)
@section('app_class', 'app-auth')
@section('content_class', 'content-auth')
@section('custom_errors', true)

@section('content')
<div class="auth-card">
    {{-- Header & Branding --}}
    <div class="auth-header">
        <div class="auth-logo-badge">🤟</div>
        <h1 class="auth-title">Masuk ke SignTeach</h1>
        <p class="auth-subtitle">Lanjutkan perjalanan belajar bahasa isyaratmu</p>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert alert-error" style="display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:1.4rem;padding:0.75rem 1rem">
            <span style="font-size:1.1rem;line-height:1">⚠️</span>
            <div style="font-size:0.88rem;line-height:1.4">{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- Form Login --}}
    <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
        @csrf

        {{-- Email / Username Field --}}
        <div class="field" style="margin-bottom:1.1rem">
            <label for="username" class="form-label">Email / Username</label>
            <div class="input-group">
                <span class="input-group-icon">✉️</span>
                <input
                    type="email"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="nama@example.com"
                    class="input-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
        </div>

        {{-- Password Field with Show/Hide Toggle --}}
        <div class="field" style="margin-bottom:1.1rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem">
                <label for="password" class="form-label" style="margin-bottom:0">Kata Sandi</label>
            </div>
            <div class="input-group">
                <span class="input-group-icon">🔒</span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    class="input-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                    required
                    autocomplete="current-password"
                >
                <button
                    type="button"
                    id="btnTogglePassword"
                    class="btn-toggle-password"
                    onclick="togglePasswordVisibility()"
                    title="Tampilkan / Sembunyikan Kata Sandi"
                    aria-label="Tampilkan kata sandi"
                >
                    👁️
                </button>
            </div>
        </div>

        {{-- Remember Me & Help --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.3rem;font-size:0.86rem">
            <label style="display:flex;align-items:center;gap:0.45rem;cursor:pointer;margin-bottom:0;font-weight:500;color:var(--text)">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer"
                >
                <span>Ingat Saya</span>
            </label>
        </div>

        {{-- Submit Button with Loading State --}}
        <button type="submit" id="btnLoginSubmit" class="btn btn-full" style="padding:0.75rem 1rem;font-size:1rem;font-weight:700">
            Masuk ke Akun 🚀
        </button>
    </form>

    {{-- Demo Account Quick Access --}}
    <div class="demo-box">
        <div class="demo-box-header">
            <span>⚡</span>
            <span>Akses Cepat Akun Demo</span>
        </div>
        <div class="demo-actions">
            <button
                type="button"
                class="demo-pill"
                onclick="fillDemoAccount('siswa@signteach.id', 'siswa123')"
                title="Isi form dengan akun Siswa demo"
            >
                🎓 Siswa (Rina)
            </button>
            <button
                type="button"
                class="demo-pill"
                onclick="fillDemoAccount('guru@signteach.id', 'guru123')"
                title="Isi form dengan akun Guru demo"
            >
                🧑‍🏫 Guru (Budi)
            </button>
        </div>
        <div style="font-size:0.75rem;color:var(--muted);text-align:center;margin-top:0.5rem">
            Klik tombol di atas untuk mengisi kredensial secara instan.
        </div>
    </div>

    {{-- Register Link --}}
    <div style="text-align:center;margin-top:1.4rem;font-size:0.9rem;color:var(--muted)">
        Belum punya akun?
        <a href="{{ route('register') }}" style="color:var(--primary);font-weight:700">Daftar di sini 📝</a>
    </div>

    {{-- Back to Home --}}
    <div style="text-align:center;margin-top:1rem">
        <a href="{{ route('home') }}" style="font-size:0.82rem;color:var(--muted);display:inline-flex;align-items:center;gap:0.3rem">
            <span>←</span> Kembali ke Halaman Utama
        </a>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const pwd = document.getElementById('password');
        const btn = document.getElementById('btnTogglePassword');
        if (!pwd || !btn) return;

        if (pwd.type === 'password') {
            pwd.type = 'text';
            btn.innerHTML = '🙈';
            btn.title = 'Sembunyikan kata sandi';
            btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
        } else {
            pwd.type = 'password';
            btn.innerHTML = '👁️';
            btn.title = 'Tampilkan kata sandi';
            btn.setAttribute('aria-label', 'Tampilkan kata sandi');
        }
    }

    function fillDemoAccount(username, password) {
        const uInput = document.getElementById('username');
        const pInput = document.getElementById('password');
        if (!uInput || !pInput) return;

        uInput.value = username;
        pInput.value = password;

        // Visual highlight feedback
        uInput.style.borderColor = 'var(--primary)';
        pInput.style.borderColor = 'var(--primary)';
        setTimeout(() => {
            uInput.style.borderColor = '';
            pInput.style.borderColor = '';
        }, 800);

        const btn = document.getElementById('btnLoginSubmit');
        if (btn) btn.focus();
    }

    document.getElementById('loginForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnLoginSubmit');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.8';
            btn.style.cursor = 'wait';
            btn.innerHTML = 'Memverifikasi akun... ⏳';
        }
    });
</script>
@endsection
