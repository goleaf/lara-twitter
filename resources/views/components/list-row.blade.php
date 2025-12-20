@props([
    'href' => null,
    'focus' => null,
    'size' => null,
])

@php
    $focus = in_array($focus, ['self', 'within'], true)
        ? $focus
        : ($href ? 'self' : 'within');

    $size = in_array($size, ['sm', 'md', 'lg'], true) ? $size : 'md';

    $paddingClasses = match ($size) {
        'lg' => 'px-4 py-3',
        'sm' => 'px-3 py-1.5',
        default => 'px-3 py-2',
    };

    $focusClasses = $focus === 'within'
        ? 'focus-within:ring-2 focus-within:ring-primary/20'
        : 'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20';

    $classes = trim("flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 {$paddingClasses} hover:bg-base-200/50 hover:border-base-300 transition {$focusClasses}");
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
