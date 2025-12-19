<div class="max-w-5xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-xl font-semibold">Explore</div>

            <form wire:submit="search" class="mt-4">
                <div class="join w-full">
                    <input
                        type="text"
                        class="input input-bordered input-sm join-item w-full"
                        placeholder="Search posts, people, hashtags"
                        wire:model="q"
                    />
                    <button type="submit" class="btn btn-primary btn-sm join-item">Search</button>
                </div>
            </form>

            <div class="mt-4 overflow-x-auto">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            @if ($tab === 'for-you')
                <div class="space-y-3">
                    @forelse ($this->forYouPosts as $post)
                        <livewire:post-card :post="$post" :key="$post->id" />
                    @empty
                        <div class="card bg-base-100 border">
                            <div class="card-body">
                                <div class="opacity-70">No recommendations yet.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            @elseif ($tab === 'trending')
                @if ($this->trendingTopicPosts->isNotEmpty())
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            @php($trendIndex = $this->trendingHashtags->keyBy('tag'))
                            <div class="font-semibold">What's happening</div>
                            <div class="space-y-4 pt-2">
                                @foreach ($this->trendingTopicPosts as $tag => $posts)
                                    @php($meta = $trendIndex->get($tag))
                                    <div class="space-y-2">
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
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            <div class="font-semibold">Trending conversations</div>
                            <div class="space-y-2 pt-2">
                                @foreach ($this->trendingConversations as $post)
                                    @php($primary = $post->repostOf && $post->body === '' ? $post->repostOf : $post)
                                    <a
                                        class="block rounded-box bg-base-200 border border-base-200 hover:border-base-300 transition px-3 py-3"
                                        href="{{ route('posts.show', ['post' => $primary]) }}"
                                        wire:navigate
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
                                <a class="flex items-center justify-between gap-3 rounded-box px-2 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('search', ['q' => $row['keyword'], 'type' => 'posts']) }}" wire:navigate>
                                    <div class="font-medium">{{ $row['keyword'] }}</div>
                                    <div class="text-right">
                                        <div class="text-sm opacity-60">{{ $row['count'] }}</div>
                                        @if ((int) ($row['recent_count'] ?? 0) > 0)
                                            <div class="text-xs opacity-60">{{ (int) $row['recent_count'] }} last hour</div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="opacity-70 text-sm">No keywords yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                @if ($tab === 'news')
                    <div class="card bg-base-100 border">
                        <div class="card-body">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold">Top stories</div>
                                <a class="link link-hover text-sm" href="{{ route('moments.index') }}" wire:navigate>All Moments</a>
                            </div>

                            <div class="space-y-2 pt-2">
                                @forelse ($this->topStories as $moment)
                                    <a class="flex items-start gap-3 rounded-box bg-base-200 border border-base-200 hover:border-base-300 transition p-3" href="{{ route('moments.show', $moment) }}" wire:navigate>
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
                                    <div class="opacity-70 text-sm">No stories yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

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
                    <div class="font-semibold">Discover people</div>
                    <div class="space-y-2 pt-2">
                        @forelse ($this->recommendedUsers as $u)
                            <div class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus-within:ring-2 focus-within:ring-primary/20">
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
                                        <div class="text-xs opacity-60 truncate">&#64;{{ $u->username }}</div>
                                        @if (($u->mutual_count ?? 0) > 0)
                                            <div class="text-xs opacity-60 truncate">
                                                {{ $u->mutual_count }} mutual follow{{ $u->mutual_count === 1 ? '' : 's' }}
                                            </div>
                                        @elseif (($u->interest_posts_count ?? 0) > 0)
                                            <div class="text-xs opacity-60 truncate">Based on your interests</div>
                                        @elseif (! is_null($u->followers_count ?? null))
                                            <div class="text-xs opacity-60 truncate">
                                                {{ $u->followers_count }} follower{{ $u->followers_count === 1 ? '' : 's' }}
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                @auth
                                    <button
                                        type="button"
                                        class="btn btn-xs {{ $this->isFollowing($u->id) ? 'btn-outline' : 'btn-primary' }}"
                                        wire:click="toggleFollow({{ $u->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleFollow({{ $u->id }})"
                                    >
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
                        Use the search bar above to find accounts, hashtags, and posts.
                        Save interest hashtags in Settings → Interests to personalize trends, and browse <a class="link link-hover" href="{{ route('moments.index') }}" wire:navigate>Moments</a> for curated stories.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
