<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Dashboard Guru</h2>
        <p class="section-sub">Selamat datang, {{ $user->name }} 👋</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Ringkasan akun — statistik kelas (total siswa, rata-rata skor,
                 latihan minggu ini, assignment aktif) masuk di Phase 7. --}}
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
                    <div class="stat-value !text-lg"><span class="badge badge-teal">Guru</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Status</div>
                    <div class="stat-value !text-lg">Aktif ✅</div>
                </div>
            </div>

            <div class="card mt-6">
                <h3 class="font-extrabold text-ink">Phase 7 — Teacher Module</h3>
                <p class="mt-1 text-sm text-ink-muted">
                    Statistik kelas (total siswa, rata-rata skor, aktivitas mingguan,
                    assignment aktif, quick actions) akan tampil di sini setelah
                    modul Teacher diimplementasikan.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
