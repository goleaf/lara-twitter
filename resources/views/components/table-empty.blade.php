@props([
    'colspan' => 1,
])

<tr>
    <td colspan="{{ (int) $colspan }}" class="p-6">
        <x-empty-state class="max-w-md mx-auto">
            @isset($icon)
                <x-slot:icon>
                    {{ $icon }}
                </x-slot:icon>
            @endisset

            {{ $slot }}
        </x-empty-state>
    </td>
</tr>
