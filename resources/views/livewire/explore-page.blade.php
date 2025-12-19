<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-xl font-semibold">Explore</div>

            <div class="tabs tabs-boxed mt-4">
                <a class="tab {{ $tab === 'trending' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'trending']) }}" wire:navigate>Trending</a>
                <a class="tab {{ $tab === 'news' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'news']) }}" wire:navigate>News</a>
                <a class="tab {{ $tab === 'sports' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'sports']) }}" wire:navigate>Sports</a>
                <a class="tab {{ $tab === 'entertainment' ? 'tab-active' : '' }}" href="{{ route('explore', ['tab' => 'entertainment']) }}" wire:navigate>Entertainment</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            @if ($tab === 'trending')
                <div class="card bg-base-100 border">
                    <div class="card-body">
                        <div class="font-semibold">Trending hashtags (24h)</div>
                        <div class="flex flex-wrap gap-2 pt-2">
                            @forelse ($this->trendingHashtags as $tag)
                                <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                                    #{{ $tag->tag }}
                                    <span class="opacity-60 ms-1">{{ $tag->uses_count }}</span>
                                </a>
                            @empty
                                <div class="opacity-70 text-sm">No hashtags yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 border">
                    <div class="card-body">
                        <div class="font-semibold">Trending keywords (24h)</div>
                        <div class="space-y-2 pt-2">
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
            @else
                <div class="space-y-3">
                    @forelse ($this->categoryPosts as $post)
                        <livewire:post-card :post="$post" :key="$post->id" />
                    @empty
                        <div class="card bg-base-100 border">
                            <div class="card-body">
                                <div class="opacity-70">No posts found for this category yet.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Recommended accounts</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->recommendedUsers as $u)
                            <div class="flex items-center justify-between gap-3">
                                <a class="min-w-0" href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
                                    <div class="font-semibold truncate">{{ $u->name }}</div>
                                    <div class="text-sm opacity-70 truncate">&#64;{{ $u->username }}</div>
                                </a>

                                @auth
                                    <button class="btn btn-outline btn-xs" wire:click="toggleFollow({{ $u->id }})">
                                        {{ $this->isFollowing($u->id) ? 'Following' : 'Follow' }}
                                    </button>
                                @endauth
                            </div>
                        @empty
                            <div class="opacity-70 text-sm">No recommendations yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Tips</div>
                    <div class="text-sm opacity-70 pt-2">
                        Try <a class="link link-hover" href="{{ route('search') }}" wire:navigate>Search</a> for accounts, hashtags, and posts.
                        Save interest hashtags in Settings → Interests to personalize trends.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

