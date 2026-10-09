@props(['title', 'subtitle' => null])

<div>
    <div class="section-mini-title">{{ $title }}</div>
    @if ($subtitle)
        <div class="section-mini-sub">{{ $subtitle }}</div>
    @endif
</div>
