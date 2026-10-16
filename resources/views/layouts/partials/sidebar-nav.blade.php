{{-- Daftar menu sidebar per grup — dipakai sidebar desktop & drawer mobile --}}
@foreach ($menuGroups as $groupLabel => $items)
    <div class="nav-section">{{ $groupLabel }}</div>
    <div class="space-y-1">
        @foreach ($items as $item)
            @if ($item['route'] === null)
                <span class="nav-link cursor-not-allowed opacity-50" aria-disabled="true">
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    <span class="flex-1">{{ $item['label'] }}</span>
                    <span class="rounded-full bg-surface px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-ink-soft">Segera</span>
                </span>
            @else
                @php($active = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ $active ? 'nav-link-active' : '' }}"
                   @if ($active) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
@endforeach
