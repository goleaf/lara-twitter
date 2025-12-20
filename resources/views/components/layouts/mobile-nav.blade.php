<div class="btm-nav btm-nav-sm mobile-nav bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur border-t border-base-200 lg:hidden z-40">
    <a
        href="{{ route('timeline') }}"
        wire:navigate
        class="{{ request()->routeIs('timeline') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
        aria-label="Home"
        @if (request()->routeIs('timeline')) aria-current="page" @endif
    >
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H15v-6a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 9 16.5v6H4.5A1.5 1.5 0 0 1 3 21v-10.5Z" />
        </svg>
        <span class="btm-nav-label">Home</span>
    </a>

    @auth
        <a
            href="{{ route('explore') }}"
            wire:navigate
            class="{{ request()->routeIs('explore') || request()->routeIs('search') || request()->routeIs('trending') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Explore"
            @if (request()->routeIs('explore') || request()->routeIs('search') || request()->routeIs('trending')) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
            </svg>
            <span class="btm-nav-label">Explore</span>
        </a>

        <a href="{{ route('timeline') }}#composer" wire:navigate class="text-primary" aria-label="New post">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313l-4.5 1.125 1.125-4.5L16.862 3.487Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75 17.25 4.5" />
            </svg>
            <span class="btm-nav-label">Post</span>
        </a>

        <a
            href="{{ route('notifications') }}"
            wire:navigate
            class="{{ request()->routeIs('notifications') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Notifications"
            @if (request()->routeIs('notifications')) aria-current="page" @endif
        >
            <div class="relative">
                <livewire:notifications.notification-badge inline />
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                </svg>
            </div>
            <span class="btm-nav-label">Notifs</span>
        </a>

        <a
            href="{{ route('messages.index') }}"
            wire:navigate
            class="{{ request()->routeIs('messages.*') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Messages"
            @if (request()->routeIs('messages.*')) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8.5 8.5 0 1 1-2.5-6.01L21 8.5V12Z" />
            </svg>
            <span class="btm-nav-label">DMs</span>
        </a>
    @else
        <a
            href="{{ route('explore') }}"
            wire:navigate
            class="{{ request()->routeIs('explore') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Explore"
            @if (request()->routeIs('explore')) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
            </svg>
            <span class="btm-nav-label">Explore</span>
        </a>

        <a
            href="{{ route('search') }}"
            wire:navigate
            class="{{ request()->routeIs('search') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Search"
            @if (request()->routeIs('search')) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 10.5 18.5a7.5 7.5 0 0 0 6.15-3.85Z" />
            </svg>
            <span class="btm-nav-label">Search</span>
        </a>

        <a
            href="{{ route('login') }}"
            wire:navigate
            class="{{ request()->routeIs('login') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
            aria-label="Login"
            @if (request()->routeIs('login')) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m9 0-3-3m3 3-3 3" />
            </svg>
            <span class="btm-nav-label">Login</span>
        </a>
    @endauth
</div>
