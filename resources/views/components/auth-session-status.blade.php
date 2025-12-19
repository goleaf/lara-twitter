@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success text-sm py-2']) }}>
        <span>{{ $status }}</span>
    </div>
@endif
