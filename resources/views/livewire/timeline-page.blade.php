<div class="space-y-4">
    @auth
        <div class="tabs tabs-boxed">
            <a
                class="tab {{ $feed === 'following' ? 'tab-active' : '' }}"
                href="{{ route('timeline', ['feed' => 'following']) }}"
                wire:navigate
            >
                Following
            </a>
            <a
                class="tab {{ $feed === 'for-you' ? 'tab-active' : '' }}"
                href="{{ route('timeline', ['feed' => 'for-you']) }}"
                wire:navigate
            >
                For You
            </a>
        </div>
    @endauth

    @guest
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="timeline-pill">
                <svg aria-hidden="true" class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>For You</span>
            </div>
            <a class="link link-primary text-sm" href="{{ route('login') }}" wire:navigate>Sign in to follow accounts</a>
        </div>
    @endguest

    @auth
        <div class="card bg-base-100 border hero-card timeline-hero">
            <div class="hero-edge" aria-hidden="true"></div>
            <div class="card-body gap-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/60">Your timeline</div>
                        <div class="text-2xl font-semibold">
                            {{ $feed === 'following' ? 'Following' : 'For You' }}
                        </div>
                        <p class="text-sm opacity-70 max-w-xl">
                            @if ($feed === 'following')
                                Posts from people you follow, with replies and reposts based on your settings.
                            @else
                                A wider mix of posts ranked by engagement and recency to help you discover new voices.
                            @endif
                        </p>
                        @php($filters = $this->timelineFilters)
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="badge badge-sm {{ $filters['replies'] ? 'badge-outline' : 'badge-ghost' }}">
                                Replies {{ $filters['replies'] ? 'shown' : 'hidden' }}
                            </span>
                            <span class="badge badge-sm {{ $filters['retweets'] ? 'badge-outline' : 'badge-ghost' }}">
                                Reposts {{ $filters['retweets'] ? 'shown' : 'hidden' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a class="timeline-pill" href="{{ route('explore') }}" wire:navigate>
                            <svg aria-hidden="true" class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.4a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Explore</span>
                        </a>
                        <a class="timeline-pill" href="{{ route('mentions') }}" wire:navigate>
                            <svg aria-hidden="true" class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8a4 4 0 11-8 0 4 4 0 018 0zM12 14a8 8 0 00-8 8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12a8 8 0 01-8 8" />
                            </svg>
                            <span>Mentions</span>
                        </a>
                        <a class="timeline-pill" href="{{ route('bookmarks') }}" wire:navigate>
                            <svg aria-hidden="true" class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3-7 3V5z" />
                            </svg>
                            <span>Bookmarks</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    @guest
        <div class="card bg-base-100 border hero-card guest-hero">
            <div class="hero-edge" aria-hidden="true"></div>
            <div class="card-body gap-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center gap-3">
                            <x-brand-mark class="h-10 w-10" />
                            <div class="text-xs font-semibold uppercase tracking-[0.4em] text-base-content/60">
                                {{ config('app.name', 'MiniTwitter') }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-3xl sm:text-4xl font-semibold leading-tight">
                                Follow the conversation, not the noise.
                            </div>
                            <div class="text-sm opacity-70 max-w-xl">
                                Browse the live feed now. Sign in to post, follow, and chat in private DMs or Spaces.
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="guest-hero-chip">Real-time posts</span>
                            <span class="guest-hero-chip">Curated trends</span>
                            <span class="guest-hero-chip">Secure DMs</span>
                            <span class="guest-hero-chip">Live Spaces</span>
                        </div>
                    </div>

                    <div class="grid gap-3 w-full sm:grid-cols-2 lg:w-72 lg:grid-cols-1">
                        <div class="guest-hero-tile">
                            <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Start here</div>
                            <div class="text-lg font-semibold">Build your feed</div>
                            <div class="text-xs opacity-70 pt-1">Follow people and topics to shape the timeline.</div>
                        </div>
                        <div class="guest-hero-tile motion-safe:animate-float">
                            <div class="text-[0.6rem] uppercase tracking-[0.35em] text-base-content/50">Explore</div>
                            <div class="text-lg font-semibold">Discover voices</div>
                            <div class="text-xs opacity-70 pt-1">Search hashtags, mentions, and threads.</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>
                    @if (Route::has('register'))
                        <a class="btn btn-outline btn-sm" href="{{ route('register') }}" wire:navigate>Create account</a>
                    @endif
                    <a class="btn btn-ghost btn-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                </div>

                <div class="text-xs opacity-70">
                    Tip: try searching for <span class="font-mono">#laravel</span>, <span class="font-mono">@username</span>, or <span class="font-mono">from:@username</span>.
                </div>
            </div>
        </div>
    @endguest

    @auth
        @if ($this->liveSpaces->isNotEmpty())
            <div class="card bg-base-100 border">
                <div class="card-body py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="font-semibold">Live Spaces</div>
                        <a class="link link-hover text-sm" href="{{ route('spaces.index') }}" wire:navigate>See all</a>
                    </div>

                    <div class="flex gap-2 overflow-x-auto pt-2 pb-1">
                        @foreach ($this->liveSpaces as $space)
                            <a
                                class="space-pill max-w-[20rem]"
                                href="{{ route('spaces.show', $space) }}"
                                wire:navigate
                                wire:key="live-space-{{ $space->id }}"
                            >
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($space->host->avatar_url)
                                            <img src="{{ $space->host->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($space->host->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-primary badge-sm motion-safe:animate-pulse">Live</span>
                                        <div class="text-xs opacity-60 truncate">&#64;{{ $space->host->username }}</div>
                                    </div>
                                    <div class="font-semibold truncate">{{ $space->title }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endauth

    @auth
        @if ($this->upcomingSpaces->isNotEmpty())
            <div class="card bg-base-100 border">
                <div class="card-body py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="font-semibold">Upcoming Spaces</div>
                        <a class="link link-hover text-sm" href="{{ route('spaces.index') }}" wire:navigate>See all</a>
                    </div>

                    <div class="flex gap-2 overflow-x-auto pt-2 pb-1">
                        @foreach ($this->upcomingSpaces as $space)
                            <a
                                class="space-pill max-w-[20rem]"
                                href="{{ route('spaces.show', $space) }}"
                                wire:navigate
                                wire:key="upcoming-space-{{ $space->id }}"
                            >
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($space->host->avatar_url)
                                            <img src="{{ $space->host->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($space->host->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-ghost badge-sm uppercase tracking-[0.2em]">Soon</span>
                                        <div class="text-xs opacity-60 truncate">&#64;{{ $space->host->username }}</div>
                                    </div>
                                    <div class="font-semibold truncate">{{ $space->title }}</div>
                                    <div class="text-xs opacity-60 truncate" title="{{ $space->scheduled_for->toDayDateTimeString() }}">
                                        Starts {{ $space->scheduled_for->diffForHumans() }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endauth

    @auth
        <div id="composer" class="scroll-mt-24">
            <livewire:post-composer />
        </div>
    @endauth

    @auth
        <div wire:poll.visible.30s="checkForNewPosts" aria-live="polite">
            @if ($hasNewPosts)
                <button
                    class="btn-announce group flex items-center justify-center gap-2"
                    wire:click="refreshTimeline"
                    wire:loading.attr="disabled"
                    wire:target="refreshTimeline"
                >
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-white/70 opacity-75 motion-safe:animate-ping"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-white"></span>
                    </span>
                    <span class="font-semibold">New posts available</span>
                    <span class="text-xs opacity-90">Refresh</span>
                    <svg aria-hidden="true" class="h-4 w-4 opacity-80 transition group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-4 4m4-4l4 4" />
                    </svg>
                </button>
            @endif
        </div>
    @endauth

    <div class="space-y-3">
        @forelse ($this->posts as $post)
            <div class="space-y-3" wire:key="timeline-post-{{ $post->id }}">
                @if ($post->reply_to_id && $post->replyTo)
                    <x-replying-to :username="$post->replyTo->user->username" />
                @endif
                <livewire:post-card :post="$post" :key="$post->id" />
            </div>
        @empty
            <div class="card bg-base-100 border empty-state-card">
                <div class="card-body items-center text-center gap-3">
                    <div class="text-lg font-semibold">Your timeline is quiet</div>
                    <p class="text-sm opacity-70 max-w-sm">
                        Follow a few people, explore topics, or share your first post to kick things off.
                    </p>
                    <div class="flex flex-wrap justify-center gap-2">
                        @auth
                            <a class="btn btn-primary btn-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                            <a class="btn btn-outline btn-sm" href="#composer">Write a post</a>
                        @endauth
                        @guest
                            <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>
                            @if (Route::has('register'))
                                <a class="btn btn-outline btn-sm" href="{{ route('register') }}" wire:navigate>Create account</a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        <livewire:shared.infinite-scroll :hasMore="$this->posts->hasMorePages()" />
    </div>
</div>
