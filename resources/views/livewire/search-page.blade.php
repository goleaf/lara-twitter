<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="text-xl font-semibold">Search</div>

            <div class="flex flex-col gap-2">
                <input
                    class="input input-bordered input-sm w-full"
                    type="text"
                    placeholder="Search posts, users, lists, #hashtags, @mentions, or URLs…"
                    wire:model.live.debounce.350ms="q"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <select class="select select-bordered select-sm w-full" wire:model.live="type">
                        <option value="all">All</option>
                        <option value="posts">Posts</option>
                        <option value="media">Media</option>
                        <option value="users">Users</option>
                        <option value="tags">Hashtags</option>
                        <option value="lists">Lists</option>
                    </select>

                    <select class="select select-bordered select-sm w-full" wire:model.live="sort">
                        <option value="latest">Latest</option>
                        <option value="top">Top</option>
                    </select>

                    <input
                        class="input input-bordered input-sm w-full"
                        type="text"
                        placeholder="From user (@username)"
                        wire:model.live.debounce.350ms="user"
                    />

                    <input class="input input-bordered input-sm w-full" type="date" wire:model.live="from" />
                    <input class="input input-bordered input-sm w-full" type="date" wire:model.live="to" />
                </div>

                <div class="text-xs opacity-70">
                    Tips: `from:alice`, `to:alice`, `since:2025-01-01`, `until:2025-01-31`, `min_faves:10`, `min_retweets:5`, `filter:verified`, `has:media`, `has:images`, `has:videos`, `has:links`, `"exact phrase"`, `laravel OR symfony`, `-exclude`.
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
                        <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="avatar">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($u->avatar_url)
                                            <img src="{{ $u->avatar_url }}" alt="" />
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
                                </div>
                            </div>

                            <div class="text-sm opacity-60 shrink-0">View</div>
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

    @if (in_array($type, ['all', 'lists'], true) && (trim($q) !== '' || $type === 'lists'))
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Lists</div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->lists as $list)
                        <a class="flex items-center justify-between gap-3 hover:bg-base-200/70 transition rounded-box px-3 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('lists.show', $list) }}" wire:navigate>
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($list->owner->avatar_url)
                                            <img src="{{ $list->owner->avatar_url }}" alt="" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($list->owner->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $list->name }}</div>
                                    <div class="text-xs opacity-60 truncate">
                                        by &#64;{{ $list->owner->username }}
                                        · {{ $list->members_count }} members
                                        · {{ $list->subscribers_count }} subscribers
                                    </div>
                                    @if ($list->description)
                                        <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm opacity-60 shrink-0">View</div>
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No lists found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if (in_array($type, ['all', 'posts', 'media'], true))
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
