<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniTwitter') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-b from-base-200 via-base-200 to-base-300/40 text-base-content">
        @php($unreadNotificationsCount = auth()->check() ? app(\App\Services\NotificationVisibilityService::class)->visibleUnreadCount(auth()->user()) : 0)

        <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5">
            <div id="navigate-progress-bar" class="h-full w-0 bg-primary opacity-0 transition-[width,opacity] duration-300"></div>
        </div>

        <div class="drawer lg:drawer-open">
            <input id="app-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content min-h-screen flex flex-col">
                <div class="sticky top-0 z-50 border-b border-base-200 bg-base-100/90 backdrop-blur supports-[backdrop-filter]:bg-base-100/70 shadow-sm">
                    <div class="navbar max-w-7xl w-full mx-auto px-4">
                        <div class="flex-none lg:hidden">
                            <label for="app-drawer" class="btn btn-ghost btn-square" aria-label="Open menu">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </label>
                        </div>

                        <div class="flex-1 min-w-0">
                            <a class="btn btn-ghost text-xl font-bold tracking-tight" href="{{ route('timeline') }}" wire:navigate>
                                MiniTwitter
                            </a>
                        </div>

                        <div class="flex-none gap-1">
                            <a class="btn btn-ghost btn-square" href="{{ route('search') }}" wire:navigate aria-label="Search">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 10.5 18.5a7.5 7.5 0 0 0 6.15-3.85Z" />
                                </svg>
                            </a>

                            <a class="btn btn-ghost btn-square" href="{{ route('trending') }}" wire:navigate aria-label="Trending">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l3-3 4 4 7-7" />
                                </svg>
                            </a>

                            <a class="btn btn-ghost btn-square" href="{{ route('explore') }}" wire:navigate aria-label="Explore">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
                                </svg>
                            </a>

                            @auth
                                <div class="indicator">
                                    @if ($unreadNotificationsCount)
                                        <span class="indicator-item badge badge-primary badge-sm">{{ $unreadNotificationsCount }}</span>
                                    @endif
                                    <a class="btn btn-ghost btn-square" href="{{ route('notifications') }}" wire:navigate aria-label="Notifications">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                                        </svg>
                                    </a>
                                </div>

                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Account menu">
                                        <div class="avatar">
                                            <div class="w-8 rounded-full border border-base-200 bg-base-100">
                                                @if (auth()->user()->avatar_url)
                                                    <img src="{{ auth()->user()->avatar_url }}" alt="" />
                                                @else
                                                    <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <ul tabindex="0" class="dropdown-content menu bg-base-100 border rounded-box shadow-lg mt-2 w-56 p-2">
                                        <li>
                                            <a href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
                                                Profile
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('profile') }}" wire:navigate>
                                                Settings
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('help.index') }}" wire:navigate>
                                                Help
                                            </a>
                                        </li>
                                        @if (auth()->user()->is_admin)
                                            <li><a href="{{ url('/admin') }}">Admin</a></li>
                                        @endif
                                        <li class="mt-1">
                                            <form method="POST" action="{{ url('/logout') }}">
                                                @csrf
                                                <button class="btn btn-ghost btn-sm w-full justify-start" type="submit">
                                                    Logout
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Login</a>
                                <a class="btn btn-ghost btn-sm" href="{{ route('register') }}" wire:navigate>Register</a>
                            @endauth
                        </div>
                    </div>
                </div>

                @if (isset($header) || $__env->hasSection('header'))
                    <header class="max-w-7xl w-full mx-auto px-4 pt-6">
                        <div class="card bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur">
                            <div class="card-body py-4">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    @yield('header')
                                @endisset
                            </div>
                        </div>
                    </header>
                @endif

                <main class="max-w-7xl w-full mx-auto px-4 pt-6 pb-20 lg:pb-6">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>

            <div class="drawer-side">
                <label for="app-drawer" class="drawer-overlay" aria-label="Close menu"></label>
                <aside class="bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur min-h-full w-72 border-r border-base-200">
                    <div class="p-4">
                        <div class="hidden lg:block pb-2">
                            <a class="btn btn-ghost text-xl w-full justify-start font-bold tracking-tight" href="{{ route('timeline') }}" wire:navigate>
                                MiniTwitter
                            </a>
                        </div>

                        @auth
                            <div class="pb-2">
                                <a class="btn btn-primary btn-sm w-full gap-2" href="{{ route('timeline') }}#composer" wire:navigate aria-label="New post">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313l-4.5 1.125 1.125-4.5L16.862 3.487Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75 17.25 4.5" />
                                    </svg>
                                    Post
                                </a>
                            </div>
                        @endauth

                        <ul class="menu p-0 gap-1">
                            <li>
                                <a href="{{ route('timeline') }}" wire:navigate class="{{ request()->routeIs('timeline') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H15v-6a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 9 16.5v6H4.5A1.5 1.5 0 0 1 3 21v-10.5Z" />
                                    </svg>
                                    <span>Home</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('explore') }}" wire:navigate class="{{ request()->routeIs('explore') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm3.5 6.5-2 6-6 2 2-6Z" />
                                    </svg>
                                    <span>Explore</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('search') }}" wire:navigate class="{{ request()->routeIs('search') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 10.5 18.5a7.5 7.5 0 0 0 6.15-3.85Z" />
                                    </svg>
                                    <span>Search</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('trending') }}" wire:navigate class="{{ request()->routeIs('trending') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l3-3 4 4 7-7" />
                                    </svg>
                                    <span>Trending</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('spaces.index') }}" wire:navigate class="{{ request()->routeIs('spaces.*') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.5a3.5 3.5 0 1 0-3.5-3.5 3.5 3.5 0 0 0 3.5 3.5ZM19 11a7 7 0 1 0-14 0v1a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3Z" />
                                    </svg>
                                    <span>Spaces</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('moments.index') }}" wire:navigate class="{{ request()->routeIs('moments.*') ? 'active' : '' }}">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a2 2 0 0 1 2 2v14l-7-3-7 3V6a2 2 0 0 1 2-2Z" />
                                    </svg>
                                    <span>Moments</span>
                                </a>
                            </li>

                            @auth
                                <li>
                                    <a href="{{ route('notifications') }}" wire:navigate class="{{ request()->routeIs('notifications') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                                        </svg>
                                        <span class="flex-1">Notifications</span>
                                        @if ($unreadNotificationsCount)
                                            <span class="badge badge-primary badge-sm">{{ $unreadNotificationsCount }}</span>
                                        @endif
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('messages.index') }}" wire:navigate class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8.5 8.5 0 1 1-2.5-6.01L21 8.5V12Z" />
                                        </svg>
                                        <span>Messages</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('bookmarks') }}" wire:navigate class="{{ request()->routeIs('bookmarks') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16Z" />
                                        </svg>
                                        <span>Bookmarks</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('lists.index') }}" wire:navigate class="{{ request()->routeIs('lists.*') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                        </svg>
                                        <span>Lists</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('mentions') }}" wire:navigate class="{{ request()->routeIs('mentions') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a4 4 0 1 0-8 0v4a4 4 0 0 0 8 0V9.5a2.5 2.5 0 1 1 5 0V12a9 9 0 1 1-18 0V8a9 9 0 1 1 18 0" />
                                        </svg>
                                        <span>Mentions</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('reports.index') }}" wire:navigate class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7V7Zm-3 3h3m10 0h3m-16 4h3m10 0h3" />
                                        </svg>
                                        <span>Reports</span>
                                    </a>
                                </li>

                                @if (auth()->user()->analytics_enabled || auth()->user()->is_admin)
                                    <li>
                                        <a href="{{ route('analytics') }}" wire:navigate class="{{ request()->routeIs('analytics*') ? 'active' : '' }}">
                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 16V8m5 8V5m5 11v-7" />
                                            </svg>
                                            <span>Analytics</span>
                                        </a>
                                    </li>
                                @endif

                                <li>
                                    <a href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                        <span>Profile</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('profile') }}" wire:navigate class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0-3.5-3.5 3.5 3.5 0 0 0 3.5 3.5ZM19.4 15a7.99 7.99 0 0 0 .1-1 7.99 7.99 0 0 0-.1-1l2.1-1.6-2-3.4-2.5 1a8.2 8.2 0 0 0-1.7-1L14.9 3H9.1L8.7 6a8.2 8.2 0 0 0-1.7 1l-2.5-1-2 3.4L4.6 13a7.99 7.99 0 0 0-.1 1c0 .34.03.67.1 1l-2.1 1.6 2 3.4 2.5-1a8.2 8.2 0 0 0 1.7 1l.4 3h5.8l.4-3a8.2 8.2 0 0 0 1.7-1l2.5 1 2-3.4L19.4 15Z" />
                                        </svg>
                                        <span>Settings</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('help.index') }}" wire:navigate class="{{ request()->routeIs('help.*') ? 'active' : '' }}">
                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M9.09 9a3 3 0 1 1 5.82 1c0 2-3 2-3 4" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-9-9 9 9 0 0 1 9 9Z" />
                                        </svg>
                                        <span>Help</span>
                                    </a>
                                </li>

                                @if (auth()->user()->is_admin)
                                    <li>
                                        <a href="{{ url('/admin') }}">
                                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v6c0 5-3 9-7 9s-7-4-7-9V7l7-4Z" />
                                            </svg>
                                            <span>Admin</span>
                                        </a>
                                    </li>
                                @endif
                            @else
                                <li><a href="{{ route('help.index') }}" wire:navigate class="{{ request()->routeIs('help.*') ? 'active' : '' }}">Help</a></li>
                                <li><a href="{{ route('login') }}" wire:navigate>Login</a></li>
                                <li><a href="{{ route('register') }}" wire:navigate>Register</a></li>
                            @endauth
                        </ul>

                        @auth
                            <div class="mt-4 pt-4 border-t border-base-200">
                                <a class="flex items-center gap-3 p-2 rounded-btn hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
                                    <div class="avatar shrink-0">
                                        <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                            @if (auth()->user()->avatar_url)
                                                <img src="{{ auth()->user()->avatar_url }}" alt="" />
                                            @else
                                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold truncate">{{ auth()->user()->name }}</div>
                                        <div class="text-sm opacity-70 truncate">&#64;{{ auth()->user()->username }}</div>
                                    </div>
                                </a>

                                <form method="POST" action="{{ url('/logout') }}" class="mt-2">
                                    @csrf
                                    <button class="btn btn-ghost btn-sm w-full justify-start" type="submit">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </aside>
            </div>
        </div>

        <div class="btm-nav btm-nav-sm bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur border-t border-base-200 lg:hidden z-40">
            <a
                href="{{ route('timeline') }}"
                wire:navigate
                class="{{ request()->routeIs('timeline') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
                aria-label="Home"
            >
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H15v-6a1.5 1.5 0 0 0-1.5-1.5h-3A1.5 1.5 0 0 0 9 16.5v6H4.5A1.5 1.5 0 0 1 3 21v-10.5Z" />
                </svg>
                <span class="btm-nav-label">Home</span>
            </a>

            @auth
                <a
                    href="{{ route('messages.index') }}"
                    wire:navigate
                    class="{{ request()->routeIs('messages.*') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
                    aria-label="Messages"
                >
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8.5 8.5 0 1 1-2.5-6.01L21 8.5V12Z" />
                    </svg>
                    <span class="btm-nav-label">DMs</span>
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
                >
                    <div class="relative">
                        @if ($unreadNotificationsCount)
                            <span class="badge badge-primary badge-xs absolute -top-1 -right-2">
                                {{ $unreadNotificationsCount }}
                            </span>
                        @endif
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <span class="btm-nav-label">Notifs</span>
                </a>

                <a
                    href="{{ route('profile.show', ['user' => auth()->user()->username]) }}"
                    wire:navigate
                    class="{{ request()->routeIs('profile.*') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
                    aria-label="Profile"
                >
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                    </svg>
                    <span class="btm-nav-label">Me</span>
                </a>
            @else
                <a
                    href="{{ route('explore') }}"
                    wire:navigate
                    class="{{ request()->routeIs('explore') ? 'active text-primary border-primary' : 'text-base-content/70' }}"
                    aria-label="Explore"
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
                >
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m9 0-3-3m3 3-3 3" />
                    </svg>
                    <span class="btm-nav-label">Login</span>
                </a>
            @endauth
        </div>
    </body>
</html>
