<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="text-xl font-semibold">Lists</div>

            <form wire:submit="create" class="space-y-3">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" class="textarea textarea-bordered mt-1 block w-full" rows="2" wire:model="description"></textarea>
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
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('lists.show', $list) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $list->name }}</div>
                                    <div class="text-sm opacity-70 truncate">by &#64;{{ $list->owner->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">
                                    {{ $list->members_count }} members{{ $list->is_private ? ' · Private' : '' }}
                                </div>
                            </div>
                        </div>
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
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('lists.show', $list) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $list->name }}</div>
                                    @if ($list->description)
                                        <div class="text-sm opacity-70 truncate">{{ $list->description }}</div>
                                    @endif
                                </div>
                                <div class="text-sm opacity-60 shrink-0">
                                    {{ $list->members_count }} members{{ $list->is_private ? ' · Private' : '' }}
                                </div>
                            </div>
                        </div>
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
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('lists.show', $list) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $list->name }}</div>
                                    <div class="text-sm opacity-70 truncate">by &#64;{{ $list->owner->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">
                                    {{ $list->members_count }} members{{ $list->is_private ? ' · Private' : '' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">You’re not a member of any lists.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
