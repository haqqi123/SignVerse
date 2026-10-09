@props(['label', 'value', 'delta' => null])

<div class="stat-card">
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value">{{ $value }}</div>
    @if ($delta)
        <div class="stat-delta">{{ $delta }}</div>
    @endif
</div>
