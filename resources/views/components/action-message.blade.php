@props(['on'])

<div x-data="{ shown: false, timeout: null }"
     x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
     x-show.transition.out.opacity.duration.1500ms="shown"
     x-transition:leave.opacity.duration.1500ms
     style="display: none;"
     role="status"
     aria-live="polite"
    {{ $attributes->merge(['class' => 'badge badge-success badge-sm gap-1']) }}
>
    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
        <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm4.03 7.47a.75.75 0 0 1 0 1.06l-4.95 4.95a.75.75 0 0 1-1.06 0l-2.05-2.05a.75.75 0 1 1 1.06-1.06l1.52 1.52 4.42-4.42a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
    </svg>
    <span class="min-w-0 truncate">{{ $slot->isEmpty() ? __('Saved.') : $slot }}</span>
</div>
