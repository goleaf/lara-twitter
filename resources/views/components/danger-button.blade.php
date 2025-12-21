<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-error btn-sm rounded-full px-4']) }}>
    {{ $slot }}
</button>
