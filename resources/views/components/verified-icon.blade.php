@props(['label' => 'Verified'])

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center w-4 h-4 rounded-full bg-primary/10 text-primary']) }}
    role="img"
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20ZM16.707 9.293a1 1 0 0 0-1.414 0L11 13.586 8.707 11.293a1 1 0 1 0-1.414 1.414l3 3a1 1 0 0 0 1.414 0l5-5a1 1 0 0 0 0-1.414Z" />
    </svg>
</span>
