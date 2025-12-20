<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xl font-semibold">Lists</div>
                    <div class="opacity-70 text-sm">&#64;{{ $user->username }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Public lists</div>
                <span class="badge badge-outline badge-sm">{{ $this->lists->total() }}</span>
            </div>

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
                                    · {{ $list->subscribers_count ?? 0 }} subscribers
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
	                        No public lists yet.
	                    </x-empty-state>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->lists->links() }}
    </div>
</div>
