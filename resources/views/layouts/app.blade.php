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
    <body class="font-sans antialiased bg-base-200">
        <div class="min-h-screen">
            <div class="navbar bg-base-100 border-b">
                <div class="max-w-5xl w-full mx-auto px-4">
                    <div class="flex-1">
                        <a class="btn btn-ghost text-xl" href="{{ route('timeline') }}" wire:navigate>MiniTwitter</a>
                    </div>

                    <div class="flex-none gap-2">
                        @auth
                            <a class="btn btn-ghost btn-sm" href="{{ route('search') }}" wire:navigate>Search</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('trending') }}" wire:navigate>Trending</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('notifications') }}" wire:navigate>Notifications</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('bookmarks') }}" wire:navigate>Bookmarks</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('mentions') }}" wire:navigate>Mentions</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>Messages</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('lists.index') }}" wire:navigate>Lists</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('spaces.index') }}" wire:navigate>Spaces</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('moments.index') }}" wire:navigate>Moments</a>
                            @if (auth()->user()->analytics_enabled || auth()->user()->is_admin)
                                <a class="btn btn-ghost btn-sm" href="{{ route('analytics') }}" wire:navigate>Analytics</a>
                            @endif
                            <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>Profile</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('profile') }}" wire:navigate>Settings</a>

                            @if (auth()->user()->is_admin)
                                <a class="btn btn-ghost btn-sm" href="{{ url('/admin') }}">Admin</a>
                            @endif

                            <form method="POST" action="{{ url('/logout') }}">
                                @csrf
                                <button class="btn btn-ghost btn-sm" type="submit">Logout</button>
                            </form>
                        @else
                            <a class="btn btn-ghost btn-sm" href="{{ route('search') }}" wire:navigate>Search</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('trending') }}" wire:navigate>Trending</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('spaces.index') }}" wire:navigate>Spaces</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('moments.index') }}" wire:navigate>Moments</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Login</a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('register') }}" wire:navigate>Register</a>
                        @endauth
                    </div>
                </div>
            </div>

            @if (isset($header) || $__env->hasSection('header'))
                <header class="max-w-5xl mx-auto px-4 pt-6">
                    <div class="card bg-base-100 border">
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

            <main class="py-6 px-4">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </body>
</html>
