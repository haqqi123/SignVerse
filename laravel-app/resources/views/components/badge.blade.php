@props(['color' => null, 'variant' => null])

@php
    $c = $color ?? $variant ?? 'gray';
    $class = match ($c) {
        'indigo' => 'badge-indigo',
        'teal' => 'badge-teal',
        'amber' => 'badge-amber',
        default => 'badge-gray',
    };
@endphp

<span class="st-badge {{ $class }}">{{ $slot }}</span>
