@props(['mobile' => false])

@php
    $itemBase = 'flex items-center gap-4 px-4 py-3 rounded-2xl transition-all font-semibold focus-ring border border-base-200/50 bg-base-100/70 supports-[backdrop-filter]:bg-base-100/60 supports-[backdrop-filter]:backdrop-blur';
    $itemActive = 'bg-primary/15 text-primary shadow-sm ring-1 ring-primary/25 border-primary/25';
    $itemInactive = 'text-base-content/70 hover:bg-base-100/90 hover:text-base-content hover:border-base-300/80 hover:shadow-sm';
@endphp

<nav class="flex flex-col h-full p-4 space-y-2" aria-label="Primary">
    <a href="{{ route('timeline') }}" wire:navigate class="flex items-center justify-center w-12 h-12 mb-2 rounded-full hover:bg-base-100/80 transition-colors focus-ring" aria-label="Home">
        <x-brand-mark class="w-8 h-8 text-primary" />
    </a>

    <a
        href="{{ route('timeline') }}"
        wire:navigate
        class="{{ $itemBase }} {{ request()->routeIs('timeline') ? $itemActive : $itemInactive }}"
        @if (request()->routeIs('timeline')) aria-current="page" @endif
    >
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-lg">Home</span>
    </a>

    <a
        href="{{ route('explore') }}"
        wire:navigate
        class="{{ $itemBase }} {{ request()->routeIs('explore') || request()->routeIs('search') || request()->routeIs('trending') ? $itemActive : $itemInactive }}"
        @if (request()->routeIs('explore') || request()->routeIs('search') || request()->routeIs('trending')) aria-current="page" @endif
    >
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
        </svg>
        <span class="text-lg">Explore</span>
    </a>

    @auth
        <a
            href="{{ route('notifications') }}"
            wire:navigate
            class="{{ $itemBase }} {{ request()->routeIs('notifications') ? $itemActive : $itemInactive }} relative"
            @if (request()->routeIs('notifications')) aria-current="page" @endif
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="text-lg">Notifications</span>
            <livewire:notifications.notification-badge inline />
        </a>

        <a
            href="{{ route('messages.index') }}"
            wire:navigate
            class="{{ $itemBase }} {{ request()->routeIs('messages.*') ? $itemActive : $itemInactive }}"
            @if (request()->routeIs('messages.*')) aria-current="page" @endif
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a8.5 8.5 0 1 1-2.5-6.01L21 8.5V12Z" />
            </svg>
            <span class="text-lg">Messages</span>
        </a>

        <a
            href="{{ route('bookmarks') }}"
            wire:navigate
            class="{{ $itemBase }} {{ request()->routeIs('bookmarks') ? $itemActive : $itemInactive }}"
            @if (request()->routeIs('bookmarks')) aria-current="page" @endif
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16Z" />
            </svg>
            <span class="text-lg">Bookmarks</span>
        </a>

        <a
            href="{{ route('profile.show', ['user' => auth()->user()->username]) }}"
            wire:navigate
            class="{{ $itemBase }} {{ request()->routeIs('profile.*') ? $itemActive : $itemInactive }}"
            @if (request()->routeIs('profile.*')) aria-current="page" @endif
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z" />
            </svg>
            <span class="text-lg">Profile</span>
        </a>

        <div class="dropdown dropdown-top dropdown-end">
            <label tabindex="0" class="{{ $itemBase }} {{ $itemInactive }} cursor-pointer">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-lg">More</span>
            </label>
            <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100/95 supports-[backdrop-filter]:bg-base-100/80 backdrop-blur rounded-box w-64 border border-base-300 mb-2">
                <li><a href="{{ route('profile') }}" wire:navigate>Settings</a></li>
                <li><a href="{{ route('lists.index') }}" wire:navigate>Lists</a></li>
                <li><a href="{{ route('mentions') }}" wire:navigate>Mentions</a></li>
                <li><a href="{{ route('reports.index') }}" wire:navigate>Reports</a></li>
                @if (auth()->user()->analytics_enabled || auth()->user()->is_admin)
                    <li><a href="{{ route('analytics') }}" wire:navigate>Analytics</a></li>
                @endif
                <li><a href="{{ route('help.index') }}" wire:navigate>Help</a></li>
                @if (auth()->user()->is_admin)
                    <li><a href="{{ url('/admin') }}">Admin</a></li>
                @endif
                <div class="divider my-0"></div>
                <li>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="text-error">Logout</button>
                    </form>
                </li>
            </ul>
        </div>

        <a
            href="{{ route('timeline') }}#composer"
            wire:navigate
            class="btn btn-twitter btn-lg w-full rounded-full text-lg font-bold mt-4 shadow-lg hover:shadow-xl transition-all"
        >
            Post
        </a>

        <div class="mt-auto">
            <div class="dropdown dropdown-top dropdown-end w-full">
                <label tabindex="0" class="flex items-center gap-3 p-3 rounded-full border border-base-200/70 bg-base-100/70 hover:bg-base-100/90 transition-colors cursor-pointer focus-ring">
                    <div class="avatar">
                        <div class="w-10 h-10 rounded-full border border-base-200 bg-base-100">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="" loading="lazy" decoding="async" />
                            @else
                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-base-content/60 truncate">&#64;{{ auth()->user()->username }}</div>
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100/95 supports-[backdrop-filter]:bg-base-100/80 backdrop-blur rounded-box w-64 border border-base-300 mb-2">
                    <li><a href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>My Profile</a></li>
                    <li><a href="{{ route('profile') }}" wire:navigate>Settings</a></li>
                    <div class="divider my-0"></div>
                    <li>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="text-error w-full text-left">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    @else
        <div class="space-y-2">
            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm w-full">Log in</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" wire:navigate class="btn btn-outline btn-sm w-full">Create account</a>
            @endif
        </div>
    @endauth
</nav>
