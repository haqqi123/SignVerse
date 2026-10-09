@props(['title', 'subtitle' => null])

<div>
    <div class="section-title">{{ $title }}</div>
    @if ($subtitle)
        <div class="section-sub">{{ $subtitle }}</div>
    @endif
</div>
