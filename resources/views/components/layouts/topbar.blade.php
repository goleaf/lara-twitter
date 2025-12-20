@props(['title' => null])

@php
    if (! $title) {
        $appName = config('app.name', 'MiniTwitter');

        $title = match (true) {
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
@endphp

<div class="flex items-center justify-between px-4 h-14">
    <h1 class="text-xl font-bold truncate">{{ $title }}</h1>

    @auth
        <div class="flex items-center gap-2">
            <a href="{{ route('timeline') }}#composer" wire:navigate class="btn btn-primary btn-sm">Post</a>
        </div>
    @endauth
</div>
