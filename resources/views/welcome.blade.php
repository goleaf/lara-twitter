@extends('layouts.app')

@section('header')
    <div class="space-y-1">
        <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/60">Welcome</div>
        <div class="text-xl font-semibold">Meet {{ config('app.name', 'MiniTwitter') }}</div>
        <div class="text-sm opacity-70">A tiny Twitter-style app built with Laravel 12, Livewire v3, Tailwind, and daisyUI.</div>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-5">
        <div class="card bg-base-100 border hero-card welcome-hero">
            <div class="hero-edge" aria-hidden="true"></div>
            <div class="card-body gap-8">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-center">
                    <div class="space-y-5">
                        <div class="flex items-center gap-3">
                            <x-brand-mark class="h-11 w-11" />
                            <div>
                                <div class="text-[0.6rem] uppercase tracking-[0.4em] text-base-content/60">
                                    {{ config('app.name', 'MiniTwitter') }}
                                </div>
                                <div class="text-sm font-semibold text-base-content/70">A calm social layer</div>
                            </div>
                        </div>

                        <div class="space-y-3 max-w-xl">
                            <div class="text-4xl sm:text-5xl font-semibold leading-tight">
                                Build a calm, focused feed for the conversations that matter.
                            </div>
                            <p class="text-sm opacity-70">
                                Post short updates, curate who you follow, and explore trends without the noise. Built with Laravel 12,
                                Livewire v3, Tailwind, and daisyUI for a fast, light-only UI.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="guest-hero-chip">Real-time posts</span>
                            <span class="guest-hero-chip">Curated trends</span>
                            <span class="guest-hero-chip">Spaces + DMs</span>
                            <span class="guest-hero-chip">Media + polls</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
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

                            <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="welcome-stat">
                                <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Signal</div>
                                <div class="text-sm font-semibold">Noise-aware ranking</div>
                                <div class="text-xs opacity-60 pt-1">Engagement plus recency, tuned for calm.</div>
                            </div>
                            <div class="welcome-stat">
                                <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Control</div>
                                <div class="text-sm font-semibold">Timeline you shape</div>
                                <div class="text-xs opacity-60 pt-1">Mute, block, and filter with precision.</div>
                            </div>
                            <div class="welcome-stat">
                                <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Focus</div>
                                <div class="text-sm font-semibold">Light-only clarity</div>
                                <div class="text-xs opacity-60 pt-1">Readable, fast, and distraction-free.</div>
                            </div>
                        </div>
                    </div>

                    <div class="welcome-preview">
                        <div class="space-y-3 pt-4">
                            <div class="welcome-preview-card">
                                <div class="flex items-center justify-between text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">
                                    <span>Daily brief</span>
                                    <span>07:45</span>
                                </div>
                                <div class="flex flex-wrap gap-2 pt-3">
                                    <span class="badge badge-outline badge-sm">#laravel</span>
                                    <span class="badge badge-outline badge-sm">#design</span>
                                    <span class="badge badge-outline badge-sm">#product</span>
                                </div>
                            </div>

                            <div class="welcome-preview-card space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full border border-base-200 bg-base-200 grid place-items-center text-xs font-semibold">T</div>
                                        <div>
                                            <div class="text-sm font-semibold">Taylor O.</div>
                                            <div class="text-xs opacity-60">&#64;taylor</div>
                                        </div>
                                    </div>
                                    <div class="text-xs opacity-60 tabular-nums">2m</div>
                                </div>
                                <div class="text-sm opacity-80">
                                    Just shipped a clean search operator update. Try: from:&#64;team has:media
                                </div>
                                <div class="flex items-center gap-4 text-xs opacity-60">
                                    <span class="inline-flex items-center gap-1">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8a8 8 0 100-16 8 8 0 000 16z" />
                                        </svg>
                                        128
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                                        </svg>
                                        34
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3-7 3V5z" />
                                        </svg>
                                        19
                                    </span>
                                </div>
                            </div>

                            <div class="welcome-preview-card">
                                <div class="flex items-center justify-between">
                                    <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Live now</div>
                                    <span class="badge badge-primary badge-sm">Space</span>
                                </div>
                                <div class="pt-3 space-y-1">
                                    <div class="text-sm font-semibold">Designing calmer timelines</div>
                                    <div class="text-xs opacity-60">with &#64;marina and &#64;alex</div>
                                </div>
                                <div class="pt-3 text-xs opacity-60 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-primary/70"></span>
                                        148 listening
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-accent/70"></span>
                                        12 speaking
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="card bg-base-100 border welcome-feature-card">
                <div class="card-body gap-3">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-primary/10 text-primary p-2">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                            </svg>
                        </span>
                        <div class="font-semibold">Write</div>
                    </div>
                    <div class="text-sm opacity-70">Posts, replies, reposts, quotes, polls, and media.</div>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-outline badge-sm">Polls</span>
                        <span class="badge badge-outline badge-sm">Media</span>
                        <span class="badge badge-outline badge-sm">Threads</span>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border welcome-feature-card">
                <div class="card-body gap-3">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-primary/10 text-primary p-2">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.4a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <div class="font-semibold">Discover</div>
                    </div>
                    <div class="text-sm opacity-70">Explore trends, search operators, Moments, and hashtags.</div>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-outline badge-sm">Trends</span>
                        <span class="badge badge-outline badge-sm">Moments</span>
                        <span class="badge badge-outline badge-sm">Lists</span>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border welcome-feature-card">
                <div class="card-body gap-3">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-primary/10 text-primary p-2">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 0 1-4-.78L3 20l1.3-3.9A7.6 7.6 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                            </svg>
                        </span>
                        <div class="font-semibold">Connect</div>
                    </div>
                    <div class="text-sm opacity-70">Follow accounts, chat in DMs, and join live Spaces.</div>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-outline badge-sm">Follows</span>
                        <span class="badge badge-outline badge-sm">Spaces</span>
                        <span class="badge badge-outline badge-sm">Messages</span>
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
