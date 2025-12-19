<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="text-xl font-semibold">Lists</div>

            <form wire:submit="create" class="space-y-3">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" class="mt-1 block w-full input-sm" wire:model="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" class="textarea textarea-bordered textarea-sm mt-1 block w-full" rows="2" wire:model="description"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_private" />
                    <span class="text-sm">Private list</span>
                </label>

                <div class="flex justify-end">
                    <button class="btn btn-primary btn-sm" type="submit">Create list</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Subscribed</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->subscribedLists as $list)
                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('lists.show', $list) }}" wire:navigate>
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
                                    · {{ $list->subscribers_count ?? 0 }} subscribers
                                    {{ $list->is_private ? ' · Private' : '' }}
                                </div>
                                @if ($list->description)
                                    <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">View</div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No subscriptions yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Your lists</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->ownedLists as $list)
                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('lists.show', $list) }}" wire:navigate>
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
                                    · {{ $list->subscribers_count ?? 0 }} subscribers
                                    {{ $list->is_private ? ' · Private' : '' }}
                                </div>
                                @if ($list->description)
                                    <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">View</div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No lists yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Lists you’re on</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->memberLists as $list)
                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('lists.show', $list) }}" wire:navigate>
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
                                    · {{ $list->subscribers_count ?? 0 }} subscribers
                                    {{ $list->is_private ? ' · Private' : '' }}
                                </div>
                                @if ($list->description)
                                    <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">View</div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">You’re not a member of any lists.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
