{{-- Kartu user di bawah sidebar: avatar inisial, nama, role, profil & keluar --}}
<div class="border-t border-surface-border p-4">
    <div class="flex items-center gap-3 rounded-2xl bg-surface p-3">
        <a href="{{ route('profile.edit') }}"
           class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary text-sm font-extrabold text-white"
           title="Profil saya">
            {{ $initials }}
        </a>
        <div class="min-w-0 flex-1">
            <a href="{{ route('profile.edit') }}" class="block truncate text-sm font-bold text-ink hover:text-primary">
                {{ auth()->user()->name }}
            </a>
            <div class="truncate text-xs text-ink-muted">{{ $isStudent ? 'Siswa' : 'Guru' }} · {{ auth()->user()->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-ink-muted transition hover:bg-red-50 hover:text-red-600"
                    title="Keluar" aria-label="Keluar">
                <x-icon name="logout" class="h-5 w-5" />
            </button>
        </form>
    </div>
</div>
