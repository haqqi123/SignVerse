@props(['emoji', 'text' => null, 'message' => null])

<div class="empty-state">
    <div class="empty-state-emoji">{{ $emoji }}</div>
    <div>{{ $text ?? $message }}</div>
</div>
