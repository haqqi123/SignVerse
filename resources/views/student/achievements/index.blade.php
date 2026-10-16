<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Achievement</h2>
        <p class="section-sub">Kumpulkan semua badge dan raih prestasi belajarmu! 🏆</p>
    </x-slot>

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

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Ringkasan Statistik --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat-card">
                    <div class="stat-label">Badge Terbuka</div>
                    <div class="stat-value">{{ count($unlocked) }}/{{ count($badges) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total XP</div>
                    <div class="stat-value">{{ number_format($xp) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Level</div>
                    <div class="stat-value" style="color: {{ $level['color'] }}">{{ $level['name'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Streak</div>
                    <div class="stat-value">{{ $streak['streak'] }} hari 🔥</div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="mb-1 flex items-center justify-between">
                    <div class="text-sm font-bold" style="color: {{ $level['color'] }}">Level: {{ $level['name'] }}</div>
                    <div class="text-xs text-ink-muted">{{ number_format($xp) }} XP</div>
                </div>
                <x-xp-bar :pct="$level['pct']" :height="8" />
            </div>

            {{-- 1. Badge Terbuka (terbaru di atas) --}}
            @if (count($unlocked) > 0)
                <div class="mt-6 flex items-baseline justify-between">
                    <h3 class="font-extrabold text-ink">Diraih</h3>
                    <span class="text-xs text-ink-muted">{{ count($unlocked) }} badge berhasil kamu buka</span>
                </div>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($unlocked as $b)
                        @php
                            $icon = $badgeIcons[$b['key']] ?? '🏆';
                            $unlockedDate = $b['unlocked_at'] ? \Carbon\Carbon::parse($b['unlocked_at'])->format('d M Y') : '-';
                        @endphp
                        <div class="card text-center">
                            <div class="text-4xl">{{ $icon }}</div>
                            <div class="mt-1 text-lg font-extrabold text-ink">{{ $b['name'] }}</div>
                            <div class="mt-0.5 text-xs text-ink-muted">{{ $b['description'] }}</div>
                            <div class="mt-2 text-xs font-bold text-teal-600">✓ Unlocked {{ $unlockedDate }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 2. Badge Terkunci + Progress Menuju Target --}}
            @if (count($locked) > 0)
                <div class="mt-8 flex items-baseline justify-between">
                    <h3 class="font-extrabold text-ink">Menuju Badge Berikutnya</h3>
                    <span class="text-xs text-ink-muted">Lanjutkan latihan untuk membuka badge ini</span>
                </div>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($locked as $b)
                        @php
                            $icon = $badgeIcons[$b['key']] ?? '🔒';
                            $p = $progress[$b['key']] ?? null;
                        @endphp
                        <div class="card text-center opacity-85">
                            <div class="text-4xl grayscale">{{ $icon }}</div>
                            <div class="mt-1 text-base font-extrabold text-ink">{{ $b['name'] }}</div>
                            <div class="mt-0.5 text-xs text-ink-muted">{{ $b['description'] }}</div>
                            <div class="mt-2 text-xs font-semibold text-ink-muted">🔒 Locked</div>
                            @if ($p)
                                @php
                                    $target = max(1, (int) $p['target']);
                                    $cur = min($target, (int) $p['current']);
                                    $pct = (int) ($cur / $target * 100);
                                @endphp
                                <div class="mt-3 text-left">
                                    <x-xp-bar :pct="$pct" :height="6" />
                                    <div class="mt-0.5 text-right text-xs text-ink-muted">{{ $cur }}/{{ $target }}</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if (count($badges) === 0)
                <div class="card mt-6 py-10 text-center">
                    <span class="text-5xl">🏆</span>
                    <p class="mt-3 text-sm text-ink-muted">Belum ada data badge yang tersedia.</p>
                </div>
            @endif

            {{-- Leaderboard XP --}}
            <div class="card mt-8">
                <h3 class="font-extrabold text-ink">🏅 Leaderboard XP</h3>
                <ol class="mt-3 space-y-2">
                    @foreach ($leaderboard as $i => $entry)
                        @php
                            $isMe = $entry['student']->user_id === auth()->user()?->id;
                        @endphp
                        <li class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm {{ $isMe ? 'bg-indigo-50 font-bold text-primary' : '' }}">
                            <span>
                                {{ [1 => '🥇', 2 => '🥈', 3 => '🥉'][$i + 1] ?? ($i + 1).'.' }}
                                {{ $entry['student']->user->name }}
                            </span>
                            <span class="text-ink-muted">{{ number_format($entry['xp']) }} XP</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
