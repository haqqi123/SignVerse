{{-- XP bar — progress bergradien SignVerse --}}
@props(['pct', 'label' => null, 'height' => 10])

<div>
    @if ($label !== null && $label !== '')
        <div class="mb-1 text-xs font-semibold text-ink-muted">{{ $label }}</div>
    @endif
    <div class="w-full overflow-hidden rounded-full bg-primary-50" style="height: {{ $height }}px"
         role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) max(0, min(100, $pct)) }}">
        <div class="xp-fill" style="width: {{ max(0, min(100, $pct)) }}%"></div>
    </div>
</div>
