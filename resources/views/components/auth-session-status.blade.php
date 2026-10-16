@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success']) }} role="status">
        <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0" />
        <span>{{ $status }}</span>
    </div>
@endif
