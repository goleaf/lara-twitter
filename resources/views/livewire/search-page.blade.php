<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="text-xl font-semibold">Search</div>

            <div class="flex flex-col gap-3">
                <label class="input input-bordered input-sm w-full flex items-center gap-2 bg-base-100/70">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 10.5 18.5a7.5 7.5 0 0 0 6.15-3.85Z" />
                    </svg>
                    <input
                        class="grow"
                        type="search"
                        placeholder="Search posts, users, lists, #hashtags, @mentions, or URLs…"
                        wire:model.live.debounce.350ms="q"
                    />
                </label>

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
                </div>

                @php($hasAdvanced = trim((string) $user) !== '' || trim((string) $from) !== '' || trim((string) $to) !== '')
                <details class="collapse collapse-arrow bg-base-200/40 border border-base-200" @if ($hasAdvanced) open @endif>
                    <summary class="collapse-title text-sm font-medium">Advanced filters</summary>
                    <div class="collapse-content pt-0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input
                                class="input input-bordered input-sm w-full"
                                type="text"
                                placeholder="From user (@username)"
                                wire:model.live.debounce.350ms="user"
                            />
                            <div class="grid grid-cols-2 gap-2 sm:col-span-1">
                                <input class="input input-bordered input-sm w-full" type="date" wire:model.live="from" />
                                <input class="input input-bordered input-sm w-full" type="date" wire:model.live="to" />
                            </div>
                        </div>

                        <div class="text-xs opacity-70 pt-3 space-y-2">
                            <div class="font-medium">Tips</div>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge badge-ghost badge-sm font-mono">from:alice</span>
                                <span class="badge badge-ghost badge-sm font-mono">to:alice</span>
                                <span class="badge badge-ghost badge-sm font-mono">since:2025-01-01</span>
                                <span class="badge badge-ghost badge-sm font-mono">until:2025-01-31</span>
                                <span class="badge badge-ghost badge-sm font-mono">min_faves:10</span>
                                <span class="badge badge-ghost badge-sm font-mono">min_retweets:5</span>
                                <span class="badge badge-ghost badge-sm font-mono">filter:verified</span>
                                <span class="badge badge-ghost badge-sm font-mono">has:media</span>
                                <span class="badge badge-ghost badge-sm font-mono">has:images</span>
                                <span class="badge badge-ghost badge-sm font-mono">has:videos</span>
                                <span class="badge badge-ghost badge-sm font-mono">has:links</span>
                                <span class="badge badge-ghost badge-sm font-mono">"exact phrase"</span>
                                <span class="badge badge-ghost badge-sm font-mono">laravel OR symfony</span>
                                <span class="badge badge-ghost badge-sm font-mono">-exclude</span>
                            </div>
                        </div>
                    </div>
                </details>
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
                        <x-empty-state class="w-full">
                            No trending tags yet.
                        </x-empty-state>
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
	                        <x-list-row href="{{ route('profile.show', ['user' => $u]) }}" wire:navigate>
	                            <div class="flex items-center gap-3 min-w-0">
	                                <div class="avatar">
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
                                </div>
	                            </div>
	
	                            <div class="text-sm opacity-60 shrink-0">View</div>
	                        </x-list-row>
	                    @empty
	                        <x-empty-state>
	                            No users found.
	                        </x-empty-state>
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
                        <x-empty-state>
                            No hashtags found.
                        </x-empty-state>
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
	                        <x-list-row href="{{ route('lists.show', $list) }}" wire:navigate>
	                            <div class="flex items-center gap-3 min-w-0">
	                                <div class="avatar shrink-0">
	                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
	                                        @if ($list->owner->avatar_url)
                                            <img src="{{ $list->owner->avatar_url }}" alt="" loading="lazy" decoding="async" />
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
	                        </x-list-row>
	                    @empty
	                        <x-empty-state>
	                            No lists found.
	                        </x-empty-state>
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
