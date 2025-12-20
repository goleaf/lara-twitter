@props(['status'])

@if ($status)
    <x-callout type="success" role="status" {{ $attributes }}>
        {{ $status }}
    </x-callout>
@endif
