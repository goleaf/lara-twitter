@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'alert alert-error py-2']) }} role="alert">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm.75 6a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0V8ZM12 17a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" />
        </svg>
        <div class="min-w-0">
            <ul class="text-sm leading-snug space-y-1">
                @foreach ((array) $messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
