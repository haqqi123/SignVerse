@props(['route', 'icon', 'title'])

@php
    $active = request()->routeIs($route);
@endphp

<a href="{{ route($route) }}" @class(['active' => $active])>
    {{ $icon }} {{ $title }}
</a>
