@php
    $baseClasses = 'btn btn-ghost btn-sm w-full justify-start font-normal';
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </button>
@endif
