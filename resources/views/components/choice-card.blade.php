@props([
    'layout' => 'stacked',
])

@php
    $layout = in_array($layout, ['stacked', 'inline'], true) ? $layout : 'stacked';

    $layoutClasses = $layout === 'inline'
        ? 'flex items-center gap-3 px-4 py-2'
        : 'flex items-start justify-between gap-4 px-4 py-3';

    $classes = trim("rounded-box border border-base-200 bg-base-200/40 cursor-pointer transition hover:bg-base-200/50 hover:border-base-300 focus-within:ring-2 focus-within:ring-primary/20 {$layoutClasses}");
@endphp

<label {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</label>
