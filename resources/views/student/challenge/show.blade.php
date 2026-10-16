<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Challenge Harian</h2>
        <p class="section-sub">Selesaikan tantangan dan raih tambahan XP setiap hari 🎯 · {{ today()->translatedFormat('l, d F Y') }}</p>
    </x-slot>

    {{-- Daftar Tantangan Hari Ini --}}
    @if (count($challenges) > 0)
        <div class="space-y-4 py-8">
            @foreach ($challenges as $ch)
                @php
                    $target = max(1, (int) $ch->target);
                    $cur = min($target, (int) $ch->progress);
                    $pct = (int) ($cur / $target * 100);
                @endphp
                <div class="card">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2 text-lg font-extrabold text-ink">
                                {{ $ch->title }}
                                @if ($ch->completed)
                                    <span class="text-teal-600">✅ Selesai!</span>
                                @endif
                            </div>
                            <div class="mt-0.5 text-sm text-ink-muted">{{ $ch->description }}</div>
                            <div class="mt-2 text-xs font-semibold text-ink-muted">
                                Progres: {{ $cur }}/{{ $target }} selesai · Target Kategori: {{ ucfirst($ch->category) }}
                            </div>
                        </div>
                        <x-badge variant="amber">+{{ $ch->reward_xp }} XP</x-badge>
                    </div>

                    <div class="mt-3">
                        <x-xp-bar :pct="$pct" :height="8" />
                    </div>

                    @if ($ch->completed)
                        <div class="mt-3 text-sm font-bold text-teal-600">
                            Bonus +{{ $ch->reward_xp }} XP telah masuk ke akunmu! 🎉
                        </div>
                    @else
                        <div class="mt-4">
                            <a href="{{ route('student.practice.index') }}" class="btn-primary !px-4 !py-2 text-sm">
                                Kerjakan Tantangan Ini 🔥
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="card py-10 text-center">
            <span class="text-5xl">🎯</span>
            <p class="mt-3 text-sm text-ink-muted">Tidak ada challenge aktif untuk hari ini. Kembali lagi besok!</p>
        </div>
    @endif

    {{-- Streak Belajar --}}
    <div class="card mt-4 py-8 text-center">
        <div class="text-sm font-black text-ink-muted">Streak Kamu Saat Ini</div>
        <div class="mt-1 text-5xl font-extrabold text-ink">{{ $streak }} 🔥 hari</div>
        <div class="mt-2 text-sm text-ink-muted">
            @if ($isToday)
                Kamu sudah berlatih hari ini! Terus jaga konsistensimu.
            @else
                Latihan hari ini untuk mempertahankan dan menambah streak belajarmu!
            @endif
        </div>
    </div>

    {{-- Riwayat Reward Challenge --}}
    <div class="card mt-6">
        <h3 class="font-extrabold text-ink">Reward yang Sudah Diraih</h3>
        <p class="mt-0.5 text-sm text-ink-muted">Daftar challenge harian yang berhasil kamu selesaikan</p>

        <div class="mt-3">
            @forelse ($rewards as $r)
                <div class="flex items-center justify-between border-b border-surface-border py-2.5">
                    <div class="flex items-center gap-3">
                        <x-badge variant="indigo">+{{ $r->reward_xp }} XP</x-badge>
                        <span class="text-sm font-bold text-ink">{{ $r->title }}</span>
                    </div>
                    <div class="text-xs text-ink-muted">
                        {{ $r->completed_at ? \Carbon\Carbon::parse($r->completed_at)->format('d M Y H:i') : '-' }}
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink-muted">
                    Selesaikan challenge harian untuk mulai mengumpulkan riwayat reward XP!
                </p>
            @endforelse
        </div>
    </div>
</x-app-layout>
