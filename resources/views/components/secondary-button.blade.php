<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-ghost btn-sm']) }}>
    {{ $slot }}
</button>
