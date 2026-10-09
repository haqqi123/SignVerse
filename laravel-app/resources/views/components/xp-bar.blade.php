@props(['pct', 'label' => null, 'height' => 10])

<div>
    @if ($label)
        <div class="xp-label">{{ $label }}</div>
    @endif
    <div class="xp-track" style="height: {{ $height }}px">
        <div class="xp-fill" style="width: {{ max(0, min(100, $pct)) }}%"></div>
    </div>
</div>
