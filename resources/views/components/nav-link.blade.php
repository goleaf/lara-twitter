@props(['active'])

@php
$classes = ($active ?? false)
            ? 'btn btn-ghost btn-sm btn-active'
            : 'btn btn-ghost btn-sm';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
