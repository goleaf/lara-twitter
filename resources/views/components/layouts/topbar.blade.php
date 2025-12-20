@props(['title' => null])

@php
    if (! $title) {
        $title = match (true) {
            request()->routeIs('timeline') => 'Home',
            request()->routeIs('explore') => 'Explore',
            request()->routeIs('search') => 'Search',
            request()->routeIs('trending') => 'Trending',
            request()->routeIs('notifications') => 'Notifications',
            request()->routeIs('messages.*') => 'Messages',
            request()->routeIs('bookmarks') => 'Bookmarks',
            request()->routeIs('lists.*') => 'Lists',
            request()->routeIs('mentions') => 'Mentions',
            request()->routeIs('reports.*') => 'Reports',
            request()->routeIs('analytics') => 'Analytics',
            request()->routeIs('profile.*') => 'Profile',
            request()->routeIs('posts.*') => 'Post',
            default => config('app.name', 'MiniTwitter'),
        };
    }
@endphp

<div class="flex items-center justify-between px-4 h-14">
    <h1 class="text-xl font-bold truncate">{{ $title }}</h1>

    @auth
        <div class="flex items-center gap-2">
            <a href="{{ route('timeline') }}#composer" wire:navigate class="btn btn-primary btn-sm">Post</a>
        </div>
    @endauth
</div>
