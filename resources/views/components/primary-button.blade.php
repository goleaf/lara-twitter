<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary btn-sm rounded-full px-4 shadow-sm hover:shadow-md focus-ring']) }}>
    {{ $slot }}
</button>
