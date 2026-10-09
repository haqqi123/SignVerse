@props(['status'])

@php
    $color = match ($status) {
        'belum dimulai' => 'gray',
        'sedang dikerjakan' => 'amber',
        'selesai' => 'teal',
        default => 'gray',
    };
@endphp

<x-badge :color="$color">{{ $status }}</x-badge>
