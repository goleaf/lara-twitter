@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state-card rounded-box px-4 py-3']) }}>
    <div class="flex items-start gap-3">
        @isset($icon)
            <div class="shrink-0 opacity-60">
                {{ $icon }}
            </div>
        @endisset

        <div class="min-w-0">
            @if ($title)
                <div class="font-semibold">{{ $title }}</div>
            @endif
            <div class="{{ $title ? 'text-sm opacity-70' : 'text-sm opacity-80' }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
