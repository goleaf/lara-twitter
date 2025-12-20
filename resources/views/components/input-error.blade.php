@props(['messages'])

@if ($messages)
    <x-callout type="error" role="alert" {{ $attributes }}>
        <ul class="leading-snug space-y-1">
            @foreach ((array) $messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-callout>
@endif
