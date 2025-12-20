<span
    class="{{ $inline ? 'absolute -top-1 -right-2 badge badge-primary badge-xs' : 'badge badge-primary badge-sm' }} {{ $count ? '' : 'hidden' }} relative"
>
    <span
        class="absolute top-0 left-0 w-1 h-1 opacity-0"
        aria-hidden="true"
        wire:poll.visible.30s
        data-livewire-poll-pausable
    ></span>
    {{ $count }}
</span>
