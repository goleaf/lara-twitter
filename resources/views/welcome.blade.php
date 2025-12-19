@extends('layouts.app')

@section('header')
    <div class="space-y-1">
        <div class="text-xl font-semibold">Welcome</div>
        <div class="text-sm opacity-70">A tiny Twitter-style app built with Laravel 12, Livewire v3, Tailwind, and daisyUI.</div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-4">
        <div class="card bg-base-100 border overflow-hidden">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="text-3xl font-bold tracking-tight">{{ config('app.name', 'MiniTwitter') }}</div>
                        <div class="text-sm opacity-70">Post, follow, search, and join Spaces — with a clean, light-only UI.</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="btn btn-primary btn-sm" href="{{ route('timeline') }}" wire:navigate>Open timeline</a>

                        @auth
                            <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => auth()->user()->username]) }}" wire:navigate>
                                View profile
                            </a>
                            <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>
                                Messages
                            </a>
                        @else
                            <a class="btn btn-ghost btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>
                            @if (Route::has('register'))
                                <a class="btn btn-outline btn-sm" href="{{ route('register') }}" wire:navigate>Create account</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-box border border-base-200 bg-base-200/40 p-4">
                        <div class="font-semibold">Write</div>
                        <div class="text-sm opacity-70 pt-1">Posts, replies, reposts, quotes, polls, and media.</div>
                    </div>
                    <div class="rounded-box border border-base-200 bg-base-200/40 p-4">
                        <div class="font-semibold">Discover</div>
                        <div class="text-sm opacity-70 pt-1">Explore trends, search operators, Moments, and hashtags.</div>
                    </div>
                    <div class="rounded-box border border-base-200 bg-base-200/40 p-4">
                        <div class="font-semibold">Connect</div>
                        <div class="text-sm opacity-70 pt-1">Follow accounts, chat in DMs, and join live Spaces.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Quick links</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3">
                        <a class="btn btn-ghost btn-sm justify-start" href="{{ route('trending') }}" wire:navigate>Trending</a>
                        <a class="btn btn-ghost btn-sm justify-start" href="{{ route('explore') }}" wire:navigate>Explore</a>
                        <a class="btn btn-ghost btn-sm justify-start" href="{{ route('spaces.index') }}" wire:navigate>Spaces</a>
                        <a class="btn btn-ghost btn-sm justify-start" href="{{ route('help.index') }}" wire:navigate>Help</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Search tips</div>
                    <div class="text-sm opacity-70 pt-2">
                        Try <span class="font-mono">#laravel</span>, <span class="font-mono">@username</span>, or operators like <span class="font-mono">from:@username</span>.
                    </div>
                    <div class="pt-3 flex flex-wrap gap-2">
                        <a class="badge badge-outline badge-sm" href="{{ route('search', ['q' => '#laravel']) }}" wire:navigate>#laravel</a>
                        <a class="badge badge-outline badge-sm" href="{{ route('search', ['q' => '#php']) }}" wire:navigate>#php</a>
                        <a class="badge badge-outline badge-sm" href="{{ route('search', ['q' => 'from:@admin']) }}" wire:navigate>from:@admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
