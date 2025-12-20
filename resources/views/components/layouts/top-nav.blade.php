@props(['title' => null])

@php
    use App\Support\PageTitle;

    $title = PageTitle::resolve($title);
    $menuShell = 'flex min-w-0 flex-nowrap items-center gap-1 overflow-x-auto scrollbar-thin px-1 py-1 rounded-full bg-base-200/70 border border-base-300/80 shadow-sm';
    $itemBase = 'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-colors whitespace-nowrap';
    $itemActive = 'bg-base-100 text-primary shadow-sm';
    $itemInactive = 'text-base-content/70 hover:bg-base-100/80 hover:text-base-content';
    $iconClass = 'w-5 h-5';
@endphp

<div class="sticky top-0 z-40 border-b border-base-300 bg-base-100/80 backdrop-blur-xl">
    <div class="max-w-[1280px] mx-auto px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 py-3">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('timeline') }}" wire:navigate class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-base-200 transition-colors" aria-label="Home">
                    <x-brand-mark class="w-7 h-7 text-primary" />
                </a>
                <div class="min-w-0">
                    <div class="topbar-kicker">{{ config('app.name', 'MiniTwitter') }}</div>
                    <h1 class="text-lg font-semibold truncate">{{ $title }}</h1>
                </div>
            </div>

            <div class="flex flex-shrink-0 items-center gap-2">
                @auth
                    <a href="{{ route('timeline') }}#composer" wire:navigate class="btn btn-twitter btn-sm gap-2">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313l-4.5 1.125 1.125-4.5L16.862 3.487Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75 17.25 4.5" />
                        </svg>
                        <span>Post</span>
                    </a>

                    <div class="dropdown dropdown-bottom dropdown-end">
                        <label tabindex="0" class="flex items-center gap-2 px-2 py-1.5 rounded-full hover:bg-base-200 transition-colors cursor-pointer">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full border border-base-200 bg-base-100">
                                    @if (auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="hidden xl:block min-w-0">
                                <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-base-content/60 truncate">&#64;{{ auth()->user()->username }}</div>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-64 border border-base-300 mt-2">
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
                @else
                    <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-sm">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" wire:navigate class="btn btn-outline btn-sm">Create account</a>
                    @endif
                @endauth
            </div>
        </div>

        <div class="border-t border-base-200/70 pt-3 pb-3">
            <nav class="{{ $menuShell }} lg:flex-wrap lg:overflow-visible">
                <a
                    href="{{ route('timeline') }}"
                    wire:navigate
                    class="{{ $itemBase }} {{ request()->routeIs('timeline') ? $itemActive : $itemInactive }}"
                >
                    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Home</span>
                </a>

                <a
                    href="{{ route('explore') }}"
                    wire:navigate
                    class="{{ $itemBase }} {{ request()->routeIs('explore') || request()->routeIs('search') || request()->routeIs('trending') ? $itemActive : $itemInactive }}"
                >
                    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
                    </svg>
                    <span>Explore</span>
                </a>

                @auth
                    <a
                        href="{{ route('notifications') }}"
                        wire:navigate
                        class="{{ $itemBase }} {{ request()->routeIs('notifications') ? $itemActive : $itemInactive }} relative"
                    >
                        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>Notifications</span>
                        <livewire:notifications.notification-badge inline />
                    </a>

                    <a
                        href="{{ route('messages.index') }}"
                        wire:navigate
                        class="{{ $itemBase }} {{ request()->routeIs('messages.*') ? $itemActive : $itemInactive }}"
                    >
                        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a8.5 8.5 0 1 1-2.5-6.01L21 8.5V12Z" />
                        </svg>
                        <span>Messages</span>
                    </a>

                    <a
                        href="{{ route('bookmarks') }}"
                        wire:navigate
                        class="{{ $itemBase }} {{ request()->routeIs('bookmarks') ? $itemActive : $itemInactive }}"
                    >
                        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16Z" />
                        </svg>
                        <span>Bookmarks</span>
                    </a>

                    <a
                        href="{{ route('profile.show', ['user' => auth()->user()->username]) }}"
                        wire:navigate
                        class="{{ $itemBase }} {{ request()->routeIs('profile.*') ? $itemActive : $itemInactive }}"
                    >
                        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z" />
                        </svg>
                        <span>Profile</span>
                    </a>

                    <div class="dropdown dropdown-bottom dropdown-end">
                        <label tabindex="0" class="{{ $itemBase }} {{ $itemInactive }} cursor-pointer">
                            <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>More</span>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-64 border border-base-300 mt-2">
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
                @endauth
            </nav>
        </div>
    </div>
</div>
