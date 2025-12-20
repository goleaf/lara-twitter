@props([
    'type' => 'info',
    'title' => null,
])

@php
    $type = in_array($type, ['info', 'success', 'warning', 'error'], true) ? $type : 'info';

    $colorClasses = match ($type) {
        'success' => 'border-success/25 bg-success/10',
        'warning' => 'border-warning/25 bg-warning/10',
        'error' => 'border-error/25 bg-error/10',
        default => 'border-info/25 bg-info/10',
    };

    $iconClasses = match ($type) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
        default => 'text-info',
    };

    $bodyClasses = $type === 'error'
        ? 'text-sm opacity-100'
        : ($title ? 'text-sm opacity-80' : 'text-sm opacity-90');
@endphp

<div {{ $attributes->merge(['class' => "rounded-box border px-4 py-3 {$colorClasses}"]) }}>
    <div class="flex items-start gap-3">
        <div class="shrink-0 {{ $iconClasses }}">
            @isset($icon)
                {{ $icon }}
            @else
                @if ($type === 'success')
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm4.03 7.47a.75.75 0 0 1 0 1.06l-4.95 4.95a.75.75 0 0 1-1.06 0l-2.05-2.05a.75.75 0 1 1 1.06-1.06l1.52 1.52 4.42-4.42a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                @elseif ($type === 'warning')
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.401 3.003a3 3 0 0 1 5.198 0l7.355 12.747A3 3 0 0 1 19.355 20H4.645a3 3 0 0 1-2.599-4.25L9.401 3.003ZM12 8.25a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                @elseif ($type === 'error')
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm.75 6a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0V8ZM12 17a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-6h2v6Zm0-8h-2V7h2v2Z" clip-rule="evenodd" />
                    </svg>
                @endif
            @endisset
        </div>

        <div class="min-w-0">
            @if ($title)
                <div class="font-semibold">{{ $title }}</div>
            @endif
            <div class="{{ $bodyClasses }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
