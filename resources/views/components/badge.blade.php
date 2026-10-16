{{-- Badge pill — dirender pakai utility Tailwind SignVerse --}}
@props(['color' => null, 'variant' => null])

@php
    $c = $color ?? $variant ?? 'gray';
    $class = match ($c) {
        'indigo' => 'badge-indigo',
        'amber' => 'badge-amber',
        'teal' => 'badge-teal',
        default => 'badge-gray',
    };
@endphp

<span class="badge {{ $class }}">{{ $slot }}</span>
