@props([
    'username',
    'navigate' => true,
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full border border-base-200/70 bg-base-100/80 px-3 py-1 text-xs font-medium text-base-content/70']) }}>
    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-primary/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 0 1-4-.78L3 20l1.3-3.9A7.6 7.6 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
    </svg>
    <span>
        Replying to
        <a class="link link-primary" href="{{ route('profile.show', ['user' => $username]) }}" @if ($navigate) wire:navigate @endif>
            &#64;{{ $username }}
        </a>
    </span>
</div>
