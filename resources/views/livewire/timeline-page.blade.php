<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="tabs tabs-boxed bg-base-100/70 border border-base-200 p-1">
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
                                        class="shrink-0 flex items-center gap-3 rounded-full bg-base-100 border border-base-200 px-3 py-2 hover:bg-base-200/70 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 max-w-[20rem]"
                                        href="{{ route('spaces.show', $space) }}"
                                        wire:navigate
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
                                                <span class="badge badge-primary badge-sm">Live</span>
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
                <div id="composer" class="scroll-mt-24">
                    <livewire:post-composer />
                </div>
            @endauth

            <div wire:poll.30s="checkForNewPosts">
                @if ($hasNewPosts)
                    <button class="btn btn-primary btn-sm w-full" wire:click="refreshTimeline">
                        New posts available — refresh
                    </button>
                @endif
            </div>

            <div class="space-y-3">
                @foreach ($this->posts as $post)
                    @if ($post->reply_to_id && $post->replyTo)
                        <div class="opacity-70 text-sm">
                            Replying to
                            <a class="link link-primary" href="{{ route('profile.show', ['user' => $post->replyTo->user->username]) }}" wire:navigate>
                                &#64;{{ $post->replyTo->user->username }}
                            </a>
                        </div>
                    @endif
                    <livewire:post-card :post="$post" :key="$post->id" />
                @endforeach
            </div>

            <div class="pt-2">
                {{ $this->posts->links() }}
            </div>
        </div>

        <div class="space-y-4 lg:sticky lg:top-6 self-start">
            <div class="card bg-base-100 border" wire:poll.visible.120s>
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">Trending</div>
                        <a class="link link-primary text-sm" href="{{ route('trending') }}" wire:navigate>View all</a>
                    </div>

                    <div class="space-y-1 pt-3">
                        @forelse ($this->trendingHashtags as $tag)
                            <a class="flex items-center justify-between hover:bg-base-200/70 transition rounded-box px-2 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                                <div class="font-medium">#{{ $tag->tag }}</div>
                                <div class="text-sm opacity-60">{{ $tag->uses_count }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No hashtags yet.</div>
                        @endforelse
                    </div>

                    <div class="divider my-2"></div>

                    <div class="space-y-1">
                        @forelse ($this->trendingKeywords as $row)
                            <a class="flex items-center justify-between hover:bg-base-200/70 transition rounded-box px-2 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                                <div class="font-medium">{{ $row['keyword'] }}</div>
                                <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No keywords yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @auth
                @if ($this->upcomingSpaces->isNotEmpty())
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold">Upcoming Spaces</div>
                                <a class="link link-primary text-sm" href="{{ route('spaces.index') }}" wire:navigate>View all</a>
                            </div>

                            <div class="space-y-1 pt-3">
                                @foreach ($this->upcomingSpaces->take(4) as $space)
                                    <a class="flex items-center justify-between gap-3 rounded-box px-2 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('spaces.show', $space) }}" wire:navigate>
                                        <div class="flex items-center gap-3 min-w-0">
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
                                                <div class="font-semibold truncate">{{ $space->title }}</div>
                                                <div class="text-xs opacity-60 truncate">Host: &#64;{{ $space->host->username }}</div>
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right space-y-1">
                                            @if ($space->scheduled_for)
                                                <span class="badge badge-outline badge-sm">Scheduled</span>
                                                <div class="text-xs opacity-60">{{ $space->scheduled_for->diffForHumans() }}</div>
                                            @else
                                                <span class="badge badge-ghost badge-sm">Unscheduled</span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($feed === 'for-you' && $this->recommendedUsers->isNotEmpty())
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold">Who to follow</div>
                                <a class="link link-primary text-sm" href="{{ route('explore') }}" wire:navigate>Explore</a>
                            </div>

                            <div class="space-y-1 pt-3">
                                @foreach ($this->recommendedUsers as $u)
                                    @php($mutualCount = (int) ($u->getAttribute('mutual_count') ?? 0))
                                    @php($interestPostsCount = (int) ($u->getAttribute('interest_posts_count') ?? 0))

                                    <div class="flex items-center justify-between gap-3 rounded-box px-2 py-2 hover:bg-base-200/70 transition focus-within:ring-2 focus-within:ring-primary/20">
                                        <a class="flex items-center gap-3 min-w-0 focus:outline-none" href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
                                            <div class="avatar shrink-0">
                                                <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                                    @if ($u->avatar_url)
                                                        <img src="{{ $u->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                                    @else
                                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                            {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-semibold truncate">
                                                    {{ $u->name }}
                                                    @if ($u->is_verified)
                                                        <x-verified-icon class="ms-1 align-middle" />
                                                    @endif
                                                </div>
                                                <div class="text-xs opacity-60 truncate">
                                                    &#64;{{ $u->username }}
                                                    ·
                                                    @if ($mutualCount)
                                                        {{ $mutualCount }} mutual
                                                    @elseif ($interestPostsCount)
                                                        {{ $interestPostsCount }} posts in your interests
                                                    @else
                                                        {{ $u->followers_count ?? 0 }} followers
                                                    @endif
                                                </div>
                                            </div>
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-primary btn-xs"
                                            wire:click="toggleFollow({{ $u->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleFollow({{ $u->id }})"
                                        >
                                            Follow
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>
