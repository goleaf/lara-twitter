<div class="max-w-5xl mx-auto space-y-4">
    @php($topHashtag = $this->trendingHashtags->first())
    @php($topKeyword = $this->trendingKeywords->first())

    <div class="card bg-base-100 border hero-card explore-hero">
        <div class="hero-edge" aria-hidden="true"></div>
        <div class="card-body gap-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-2xl font-semibold">Explore</div>
                    <p class="text-sm opacity-70">Track the pulse, discover new voices, and dive into curated moments.</p>
                </div>

                <div class="flex flex-wrap gap-2 explore-spotlight">
                    @if ($topHashtag)
                        <a class="explore-pill" href="{{ route('hashtags.show', ['tag' => $topHashtag->tag]) }}" wire:navigate>
                            <svg class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18" />
                            </svg>
                            <span class="text-[0.65rem] uppercase tracking-[0.2em] opacity-60">Trending</span>
                            <span class="font-semibold">#{{ $topHashtag->tag }}</span>
                            <span class="text-xs opacity-60">{{ (int) $topHashtag->uses_count }} posts</span>
                        </a>
                    @endif
                    @if ($topKeyword)
                        <a class="explore-pill" href="{{ route('search', ['q' => $topKeyword['keyword'], 'type' => 'posts']) }}" wire:navigate>
                            <svg class="h-4 w-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-[0.65rem] uppercase tracking-[0.2em] opacity-60">Buzzing</span>
                            <span class="font-semibold">{{ $topKeyword['keyword'] }}</span>
                            <span class="text-xs opacity-60">{{ (int) $topKeyword['count'] }} mentions</span>
                        </a>
                    @endif
                </div>
            </div>

            <form wire:submit="search">
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1 explore-search">
                        <svg class="w-4 h-4 text-base-content/60 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.4a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            class="input input-bordered w-full pl-10 explore-search-input"
                            placeholder="Search posts, people, hashtags"
                            aria-label="Search posts, people, hashtags"
                            wire:model.debounce.300ms="q"
                        />
                    </div>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="search"
                    >
                        Search
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <div class="tabs tabs-boxed flex-nowrap w-max min-w-full">
                    <a class="tab {{ $tab === 'for-you' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'for-you']) }}" wire:navigate>For You</a>
                    <a class="tab {{ $tab === 'trending' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'trending']) }}" wire:navigate>Trending</a>
                    <a class="tab {{ $tab === 'news' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'news']) }}" wire:navigate>News</a>
                    <a class="tab {{ $tab === 'politics' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'politics']) }}" wire:navigate>Politics</a>
                    <a class="tab {{ $tab === 'sports' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'sports']) }}" wire:navigate>Sports</a>
                    <a class="tab {{ $tab === 'entertainment' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'entertainment']) }}" wire:navigate>Entertainment</a>
                    <a class="tab {{ $tab === 'technology' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'technology']) }}" wire:navigate>Technology</a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        @if ($topHashtag)
            <a class="explore-stat" href="{{ route('hashtags.show', ['tag' => $topHashtag->tag]) }}" wire:navigate>
                <svg class="absolute right-3 top-3 h-4 w-4 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18" />
                </svg>
                <div class="text-[0.6rem] uppercase tracking-[0.2em] text-base-content/60">Top hashtag</div>
                <div class="text-lg font-semibold">#{{ $topHashtag->tag }}</div>
                <div class="text-xs text-base-content/60">{{ (int) $topHashtag->uses_count }} posts in 24h</div>
            </a>
        @else
            <div class="explore-stat">
                <svg class="absolute right-3 top-3 h-4 w-4 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18" />
                </svg>
                <div class="text-[0.6rem] uppercase tracking-[0.2em] text-base-content/60">Top hashtag</div>
                <div class="text-sm font-semibold text-base-content/70">No trends yet</div>
            </div>
        @endif

        @if ($topKeyword)
            <a class="explore-stat" href="{{ route('search', ['q' => $topKeyword['keyword'], 'type' => 'posts']) }}" wire:navigate>
                <svg class="absolute right-3 top-3 h-4 w-4 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <div class="text-[0.6rem] uppercase tracking-[0.2em] text-base-content/60">Top keyword</div>
                <div class="text-lg font-semibold">{{ $topKeyword['keyword'] }}</div>
                <div class="text-xs text-base-content/60">{{ (int) $topKeyword['count'] }} mentions in 24h</div>
            </a>
        @else
            <div class="explore-stat">
                <svg class="absolute right-3 top-3 h-4 w-4 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <div class="text-[0.6rem] uppercase tracking-[0.2em] text-base-content/60">Top keyword</div>
                <div class="text-sm font-semibold text-base-content/70">No buzz yet</div>
            </div>
        @endif

        <a class="explore-stat" href="{{ route('moments.index') }}" wire:navigate>
            <svg class="absolute right-3 top-3 h-4 w-4 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h10a2 2 0 012 2v14l-7-3-7 3V6a2 2 0 012-2z" />
            </svg>
            <div class="text-[0.6rem] uppercase tracking-[0.2em] text-base-content/60">Moments</div>
            <div class="text-lg font-semibold">Curated stories</div>
            <div class="text-xs text-base-content/60">Browse the latest highlights</div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            @if ($tab === 'for-you')
                <div class="space-y-3">
                    @forelse ($this->forYouPosts as $post)
                        <livewire:post-card :post="$post" :key="$post->id" />
                    @empty
                        <x-empty-state>
                            No recommendations yet.
                        </x-empty-state>
                    @endforelse
                </div>
            @elseif ($tab === 'trending')
                @if ($this->trendingTopicPosts->isNotEmpty() || $this->trendingConversations->isNotEmpty())
                    <div class="grid gap-4 lg:grid-cols-2">
                        @if ($this->trendingTopicPosts->isNotEmpty())
                            <div class="card card-hover bg-base-100 border">
                                <div class="card-body">
                                    @php($trendIndex = $this->trendingHashtags->keyBy('tag'))
                                    <div class="font-semibold">What's happening</div>
                                    <div class="space-y-4 pt-2">
                                        @foreach ($this->trendingTopicPosts as $tag => $posts)
                                            @php($meta = $trendIndex->get($tag))
                                            <div class="space-y-2" wire:key="trending-topic-{{ $tag }}">
                                                <a class="link link-hover font-semibold" href="{{ route('hashtags.show', ['tag' => $tag]) }}" wire:navigate>
                                                    #{{ $tag }}
                                                </a>
                                                @if ($meta)
                                                    <div class="text-xs opacity-60">
                                                        {{ (int) ($meta->users_count ?? 0) }} people talking
                                                        @if ((int) ($meta->recent_uses_count ?? 0) > 0)
                                                            · {{ (int) $meta->recent_uses_count }} posts in the last hour
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="space-y-2">
                                                    @foreach ($posts as $post)
                                                        @php($primary = $post->repostOf && $post->body === '' ? $post->repostOf : $post)
                                                        <a
                                                            class="block rounded-box bg-base-200 border border-base-200 hover:border-base-300 transition px-3 py-3"
                                                            href="{{ route('posts.show', ['post' => $primary]) }}"
                                                            wire:navigate
                                                            wire:key="trending-topic-post-{{ $tag }}-{{ $primary->id }}"
                                                        >
                                                            <div class="text-xs opacity-70 truncate">
                                                                {{ $primary->user->name }} · &#64;{{ $primary->user->username }}
                                                            </div>
                                                            <div class="font-medium pt-1">
                                                                {{ \Illuminate\Support\Str::limit($primary->body, 140) }}
                                                            </div>
                                                            <div class="text-xs opacity-60 pt-1">
                                                                {{ $primary->created_at->diffForHumans() }}
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($this->trendingConversations->isNotEmpty())
                            <div class="card card-hover bg-base-100 border">
                                <div class="card-body">
                                    <div class="font-semibold">Trending conversations</div>
                                    <div class="space-y-2 pt-2">
                                        @foreach ($this->trendingConversations as $post)
                                            @php($primary = $post->repostOf && $post->body === '' ? $post->repostOf : $post)
                                            <a
                                                class="block rounded-box bg-base-200 border border-base-200 hover:border-base-300 transition px-3 py-3"
                                                href="{{ route('posts.show', ['post' => $primary]) }}"
                                                wire:navigate
                                                wire:key="trending-conversation-{{ $primary->id }}"
                                            >
                                                <div class="text-xs opacity-70 truncate">
                                                    {{ $primary->user->name }} · &#64;{{ $primary->user->username }}
                                                </div>
                                                <div class="font-medium pt-1">
                                                    {{ \Illuminate\Support\Str::limit($primary->body, 140) }}
                                                </div>
                                                <div class="text-xs opacity-60 pt-1">
                                                    {{ $primary->created_at->diffForHumans() }}
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="card card-hover bg-base-100 border">
                        <div class="card-body">
                            <div class="font-semibold">Trending hashtags (24h)</div>
                            <div class="flex flex-wrap gap-2 pt-2">
                                @forelse ($this->trendingHashtags as $tag)
                                    <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate wire:key="trending-hashtag-{{ $tag->id }}">
                                        #{{ $tag->tag }}
                                        <span class="opacity-60 ms-1">{{ $tag->uses_count }}</span>
                                    </a>
                                @empty
                                    <x-empty-state class="w-full">
                                        No hashtags yet.
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="card card-hover bg-base-100 border">
                        <div class="card-body">
                            <div class="font-semibold">Trending keywords (24h)</div>
                            <div class="space-y-2 pt-2">
                                @forelse ($this->trendingKeywords as $row)
                                    <x-list-row href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate wire:key="trending-keyword-{{ $row['keyword'] }}">
                                        <div class="min-w-0">
                                            <div class="font-medium truncate">{{ $row['keyword'] }}</div>
                                        </div>
                                        <div class="text-right shrink-0 tabular-nums">
                                            <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                                            @if ((int) ($row['recent_count'] ?? 0) > 0)
                                                <div class="text-xs opacity-60">{{ (int) $row['recent_count'] }} last hour</div>
                                            @endif
                                        </div>
                                    </x-list-row>
                                @empty
                                    <x-empty-state>
                                        No keywords yet.
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @if ($tab === 'news')
                    <div class="card card-hover bg-base-100 border">
                        <div class="card-body">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold">Top stories</div>
                                <a class="link link-hover text-sm" href="{{ route('moments.index') }}" wire:navigate>All Moments</a>
                            </div>

                            <div class="space-y-2 pt-2">
                                @forelse ($this->topStories as $moment)
                                    <a class="flex items-start gap-3 rounded-box bg-base-200 border border-base-200 hover:border-base-300 transition p-3" href="{{ route('moments.show', $moment) }}" wire:navigate wire:key="top-story-{{ $moment->id }}">
                                        @php($cover = $moment->coverUrl())
                                        @if ($cover)
                                            <div class="w-16 h-16 rounded-box border border-base-200 bg-base-200 overflow-hidden shrink-0">
                                                <img class="w-full h-full object-cover" src="{{ $cover }}" alt="{{ $moment->title }} cover image" loading="lazy" decoding="async" />
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-semibold truncate">{{ $moment->title }}</div>
                                                    <div class="text-sm opacity-70 truncate">
                                                        by &#64;{{ $moment->owner->username }} · {{ $moment->items_count }} posts
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($moment->firstItem && $moment->firstItem->post)
                                                @php($p = $moment->firstItem->post)
                                                @php($primary = $p->repostOf && $p->body === '' ? $p->repostOf : $p)
                                                @if ($primary->body !== '')
                                                    <div class="text-sm opacity-80 pt-1">
                                                        {{ \Illuminate\Support\Str::limit($primary->body, 140) }}
                                                    </div>
                                                @endif
                                            @elseif ($moment->description)
                                                <div class="text-sm opacity-80 pt-1">
                                                    {{ \Illuminate\Support\Str::limit($moment->description, 140) }}
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <x-empty-state>
                                        No stories yet.
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    @forelse ($this->categoryPosts as $post)
                        <livewire:post-card :post="$post" :key="$post->id" />
                    @empty
                        <x-empty-state>
                            No posts found for this category yet.
                        </x-empty-state>
                    @endforelse
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="card card-hover bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Tips</div>
                    <div class="text-sm opacity-70 pt-2">
                        Use the search bar above to find accounts, hashtags, and posts.
                        Save interest hashtags in Settings → Interests to personalize trends, and browse <a class="link link-hover" href="{{ route('moments.index') }}" wire:navigate>Moments</a> for curated stories.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
