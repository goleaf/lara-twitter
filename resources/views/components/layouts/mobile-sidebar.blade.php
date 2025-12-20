<div
    x-data="{ open: false }"
    x-on:open-sidebar.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="lg:hidden fixed inset-0 z-50"
    style="display: none;"
>
    <div class="absolute inset-0 bg-black/30" @click="open = false"></div>

    <div class="absolute left-0 top-0 h-full w-72 bg-base-100 border-r border-base-300 shadow-xl">
        <div class="h-full overflow-y-auto">
            <x-layouts.sidebar mobile />
        </div>
    </div>
</div>
