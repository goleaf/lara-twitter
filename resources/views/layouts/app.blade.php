<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $appName = config('app.name', 'MiniTwitter');
            $resolvedTitle = $pageTitle ?? $title ?? null;

            if (! $resolvedTitle) {
                $resolvedTitle = match (true) {
                    request()->routeIs('timeline') => 'Home',
                    request()->routeIs('explore') => 'Explore',
                    request()->routeIs('search') => 'Search',
                    request()->routeIs('trending') => 'Trending',

                    request()->routeIs('hashtags.show') => (function (): string {
                        $tag = (string) request()->route('tag', '');
                        $tag = ltrim($tag, '#');

                        return $tag !== '' ? "#{$tag}" : 'Hashtag';
                    })(),

                    request()->routeIs('notifications') => 'Notifications',
                    request()->routeIs('messages.*') => 'Messages',
                    request()->routeIs('bookmarks') => 'Bookmarks',

                    request()->routeIs('lists.show') => (function (): string {
                        $list = request()->route('list');

                        return $list instanceof \App\Models\UserList
                            ? $list->name
                            : 'List';
                    })(),
                    request()->routeIs('lists.*') => 'Lists',

                    request()->routeIs('mentions') => 'Mentions',
                    request()->routeIs('reports.*') => 'Reports',
                    request()->routeIs('analytics') => 'Analytics',
                    request()->routeIs('profile') => 'Settings',

                    request()->routeIs('help.*') => 'Help',

                    request()->routeIs('profile.*') => (function (): string {
                        $user = request()->route('user');

                        return $user instanceof \App\Models\User
                            ? $user->name
                            : 'Profile';
                    })(),

                    request()->routeIs('spaces.show') => (function (): string {
                        $space = request()->route('space');

                        return $space instanceof \App\Models\Space
                            ? $space->title
                            : 'Space';
                    })(),
                    request()->routeIs('spaces.*') => 'Spaces',

                    request()->routeIs('moments.show') => (function (): string {
                        $moment = request()->route('moment');

                        return $moment instanceof \App\Models\Moment
                            ? $moment->title
                            : 'Moment';
                    })(),
                    request()->routeIs('moments.*') => 'Moments',

                    request()->routeIs('posts.*') => 'Post',

                    request()->routeIs('login') => 'Log in',
                    request()->routeIs('register') => 'Create account',
                    request()->routeIs('password.request') => 'Forgot password',
                    request()->routeIs('password.reset') => 'Reset password',
                    request()->routeIs('verification.notice') => 'Verify email',
                    request()->routeIs('password.confirm') => 'Confirm password',

                    default => $appName,
                };
            }

            $documentTitle = $resolvedTitle === $appName
                ? $appName
                : "{$resolvedTitle} · {$appName}";
        @endphp

        <title>{{ $documentTitle }}</title>

        <script>
            window.AppConfig = @json([
                'userId' => auth()->check() ? auth()->id() : null,
            ]);
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @filamentStyles
        @livewireStyles
    </head>
    <body class="antialiased bg-base-100 text-base-content">
        <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5">
            <div id="navigate-progress-bar" class="h-full w-0 bg-primary opacity-0 transition-[width,opacity] duration-300"></div>
        </div>

        @php($topbarTitle = $resolvedTitle)
        @php($isTest = app()->runningUnitTests())

        <div class="hidden lg:flex min-h-screen max-w-[1280px] mx-auto">
            <div class="w-[275px] flex-shrink-0 border-r border-base-300 sticky top-0 h-screen">
                <x-layouts.sidebar />
            </div>

            <div class="flex-1 max-w-[600px] border-r border-base-300">
                <div class="sticky top-0 z-30 backdrop-blur-xl bg-base-100/80 border-b border-base-300">
                    <x-layouts.topbar :title="$topbarTitle" />
                </div>

                <main class="min-h-screen px-4 py-4 space-y-4">
                    @if (isset($header) || $__env->hasSection('header'))
                        <div class="card bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur">
                            <div class="card-body py-4">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    @yield('header')
                                @endisset
                            </div>
                        </div>
                    @endif

                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>

            @unless ($isTest)
                <div class="hidden xl:block w-[350px] flex-shrink-0">
                    <div class="sticky top-0 pt-2 px-4 space-y-4">
                        <livewire:search.global-search />

                        <livewire:widgets.trending-topics-widget />

                        <livewire:widgets.who-to-follow-widget />

                        <div class="text-xs text-base-content/60 space-y-2 px-4">
                            <div class="flex flex-wrap gap-x-3 gap-y-1">
                                <a href="{{ route('help.index') }}" class="hover:underline" wire:navigate>Help</a>
                                <a href="/terms" class="hover:underline">Terms</a>
                                <a href="/privacy" class="hover:underline">Privacy</a>
                                <a href="/cookies" class="hover:underline">Cookies</a>
                                <a href="/about" class="hover:underline">About</a>
                            </div>
                            <div>Copyright {{ date('Y') }} {{ config('app.name', 'MiniTwitter') }}</div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>

        @unless ($isTest)
        <div class="lg:hidden min-h-screen pb-16">
            <div class="sticky top-0 z-30 backdrop-blur-xl bg-base-100/80 border-b border-base-300">
                <div class="flex items-center justify-between px-4 h-14">
                    <button class="btn btn-ghost btn-circle" aria-label="Open menu" @click="$dispatch('open-sidebar')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-xl font-bold truncate">{{ $topbarTitle }}</h1>

                    @auth
                        <div class="relative">
                            <a class="btn btn-ghost btn-circle" href="{{ route('notifications') }}" wire:navigate aria-label="Notifications">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </a>
                            <livewire:notifications.notification-badge inline />
                        </div>
                    @else
                        <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>
                    @endauth
                </div>
            </div>

            <main class="px-4 py-4 space-y-4">
                @if (isset($header) || $__env->hasSection('header'))
                    <div class="card bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur">
                        <div class="card-body py-4">
                            @isset($header)
                                {{ $header }}
                            @else
                                @yield('header')
                            @endisset
                        </div>
                    </div>
                @endif

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

            <x-layouts.mobile-nav />
        </div>

        <x-layouts.mobile-sidebar />
        @endunless

        <div id="modal-container"></div>

        @filamentScripts
        @livewireScripts
    </body>
</html>
