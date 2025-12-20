@props([
    'href' => null,
    'focus' => null,
])

@php
    $focus = in_array($focus, ['self', 'within'], true)
        ? $focus
        : ($href ? 'self' : 'within');

    $focusClasses = $focus === 'within'
        ? 'focus-within:ring-2 focus-within:ring-primary/20'
        : 'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20';

    $classes = trim("flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition {$focusClasses}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif
