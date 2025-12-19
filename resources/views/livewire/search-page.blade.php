<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="text-xl font-semibold">Search</div>

            <div class="flex flex-col gap-2">
                <input
                    class="input input-bordered w-full"
                    type="text"
                    placeholder="Search posts, users, #hashtags, @mentions…"
                    wire:model.live.debounce.350ms="q"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <select class="select select-bordered w-full" wire:model.live="type">
                        <option value="all">All</option>
                        <option value="posts">Posts</option>
                        <option value="users">Users</option>
                        <option value="tags">Hashtags</option>
                    </select>

                    <select class="select select-bordered w-full" wire:model.live="sort">
                        <option value="latest">Latest</option>
                        <option value="top">Top</option>
                    </select>

                    <input
                        class="input input-bordered w-full"
                        type="text"
                        placeholder="From user (@username)"
                        wire:model.live.debounce.350ms="user"
                    />

                    <input class="input input-bordered w-full" type="date" wire:model.live="from" />
                    <input class="input input-bordered w-full" type="date" wire:model.live="to" />
                </div>

                <div class="text-xs opacity-70">
                    Tips: `from:alice`, `to:alice`, `since:2025-01-01`, `until:2025-01-31`, `min_likes:10`, `min_retweets:5`, `has:images`, `has:links`, `\"exact phrase\"`, `-exclude`.
                </div>
            </div>
        </div>
    </div>

    @if (trim($q) === '')
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
                        <div class="opacity-70 text-sm">No trending tags yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if (in_array($type, ['all', 'users'], true))
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Users</div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->users as $u)
                        <a class="flex items-center justify-between gap-4 hover:bg-base-200 rounded-box px-2 py-2" href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $u->name }}</div>
                                <div class="text-sm opacity-70 truncate">&#64;{{ $u->username }}</div>
                            </div>
                            <div class="text-sm opacity-60">View</div>
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No users found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if (in_array($type, ['all', 'tags'], true) && trim($q) !== '')
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Hashtags</div>
                <div class="flex flex-wrap gap-2 pt-2">
                    @forelse ($this->hashtags as $tag)
                        <a class="badge badge-outline" href="{{ route('hashtags.show', ['tag' => $tag->tag]) }}" wire:navigate>
                            #{{ $tag->tag }}
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No hashtags found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if (in_array($type, ['all', 'posts'], true))
        <div class="space-y-3">
            @foreach (($this->posts ?? []) as $post)
                <livewire:post-card :post="$post" :key="$post->id" />
            @endforeach
        </div>

        @if ($this->posts)
            <div class="pt-2">
                {{ $this->posts->links() }}
            </div>
        @endif
    @endif
</div>
