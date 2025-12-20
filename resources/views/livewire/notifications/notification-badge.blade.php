<span
    wire:poll.30s
    class="{{ $inline ? 'absolute -top-1 -right-2 badge badge-primary badge-xs' : 'badge badge-primary badge-sm' }} {{ $count ? '' : 'hidden' }}"
>
    {{ $count }}
</span>
