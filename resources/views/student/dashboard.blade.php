<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Dashboard Siswa</h2>
        <p class="section-sub">Selamat datang, {{ $user->name }} 👋</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Ringkasan akun — statistik pembelajaran penuh (XP, level,
                 streak, progress) masuk di Phase 3 via StudentDashboardService. --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">Nama</div>
                    <div class="stat-value !text-lg">{{ $user->name }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Email</div>
                    <div class="stat-value !text-lg">{{ $user->email }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Role</div>
                    <div class="stat-value !text-lg"><span class="badge badge-indigo">Siswa</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Status</div>
                    <div class="stat-value !text-lg">Aktif ✅</div>
                </div>
            </div>

            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Phase 3 — Student Module</h3>
                <p class="mt-1 text-sm text-ink-muted">
                    Statistik belajar (XP, level, streak, progress, rekomendasi AI, daily challenge)
                    akan tampil di sini setelah modul Student diimplementasikan.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
