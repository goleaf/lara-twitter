<div
    x-data="{ open: false }"
    x-on:open-sidebar.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-on:livewire:navigated.window="open = false"
    x-show="open"
    x-cloak
    id="mobile-sidebar"
    class="lg:hidden fixed inset-0 z-50"
    role="dialog"
    aria-modal="true"
    aria-label="Mobile navigation"
    style="display: none;"
>
    <div class="absolute inset-0 bg-black/30" @click="open = false" aria-hidden="true"></div>

    <div class="absolute left-0 top-0 h-full w-72 bg-base-100/95 supports-[backdrop-filter]:bg-base-100/80 backdrop-blur border-r border-base-300 shadow-xl">
        <div class="h-full overflow-y-auto">
            <x-layouts.sidebar mobile />
        </div>
    </div>
</div>
