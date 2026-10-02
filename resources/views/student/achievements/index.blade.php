<x-app-layout>
    <x-slot name="header">
        <h2 class="section-title">Achievement</h2>
        <p class="section-sub">Koleksi badge — bukti perjalanan belajarmu.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Grid badge --}}
                <div class="lg:col-span-2">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($board as $entry)
                            @php
                                $achievement = $entry['achievement'];
                                $unlocked = $entry['unlocked_at'] !== null;
                            @endphp
                            <div class="card text-center {{ $unlocked ? '!border-amber-300' : 'opacity-60' }}">
                                <div class="text-4xl {{ $unlocked ? '' : 'grayscale' }}">{{ $achievement->icon ?? '🏅' }}</div>
                                <h3 class="mt-2 font-extrabold text-ink">{{ $achievement->name }}</h3>
                                <p class="mt-1 text-xs text-ink-muted">{{ $achievement->description }}</p>

                                <div class="mt-3">
                                    @if ($unlocked)
                                        <span class="badge badge-teal !text-xs">
                                            ✓ {{ $entry['unlocked_at']->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="badge badge-gray !text-xs">
                                            🔒 {{ ucfirst($achievement->type) }}{{ $achievement->threshold ? ' ≥ '.$achievement->threshold : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="card sm:col-span-2 lg:col-span-3 text-center">
                                <p class="text-ink-muted">Belum ada achievement didefinisikan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Sidebar: ringkasan + leaderboard --}}
                <div class="space-y-6">
                    <div class="card text-center">
                        <div class="stat-label">Level & XP</div>
                        <div class="text-4xl font-extrabold text-primary">{{ $summary['level'] }}</div>
                        <div class="text-xs text-ink-muted">{{ number_format($summary['xp']) }} XP · {{ $summary['streak'] }} hari streak 🔥</div>
                    </div>

                    <div class="card">
                        <h3 class="font-extrabold text-ink">🏅 Leaderboard XP</h3>
                        <ol class="mt-3 space-y-2">
                            @foreach ($leaderboard as $i => $entry)
                                @php
                                    $isMe = $entry['student']->user_id === auth()->user()->student?->id;
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
        </div>
    </div>
</x-app-layout>
