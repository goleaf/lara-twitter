<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
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
                                        class="shrink-0 rounded-full bg-secondary text-secondary-content px-4 py-2 border border-secondary/30 hover:opacity-90 transition max-w-[18rem]"
                                        href="{{ route('spaces.show', $space) }}"
                                        wire:navigate
                                    >
                                        <div class="text-xs opacity-80">Live &middot; &#64;{{ $space->host->username }}</div>
                                        <div class="font-semibold truncate">{{ $space->title }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            @auth
                <livewire:post-composer />
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
                            <a class="flex items-center justify-between hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
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
                            <a class="flex items-center justify-between hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                                <div class="font-medium">{{ $row['keyword'] }}</div>
                                <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                            </a>
                        @empty
                            <div class="opacity-70 text-sm">No keywords yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
