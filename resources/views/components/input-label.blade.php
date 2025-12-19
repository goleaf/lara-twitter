@props(['value'])

<label {{ $attributes->merge(['class' => 'label py-1']) }}>
    <span class="label-text font-medium">
        {{ $value ?? $slot }}
    </span>
</label>
