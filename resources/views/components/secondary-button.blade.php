<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-ghost btn-sm rounded-full px-4 focus-ring']) }}>
    {{ $slot }}
</button>
