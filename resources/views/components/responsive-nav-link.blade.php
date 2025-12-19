@props(['active'])

@php
$classes = ($active ?? false)
            ? 'btn btn-ghost btn-sm btn-active w-full justify-start font-normal'
            : 'btn btn-ghost btn-sm w-full justify-start font-normal';
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
