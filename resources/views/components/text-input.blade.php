@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'input input-bordered disabled:opacity-60 disabled:cursor-not-allowed']) }}>
