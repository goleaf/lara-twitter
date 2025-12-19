<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-xl font-semibold">Moments <span class="badge badge-ghost badge-sm align-middle">Legacy</span></div>
            <div class="text-sm opacity-70 pt-1">Curated collections of posts with a title, description, and cover image.</div>
        </div>
    </div>

    @auth
        @if ($this->canCreate)
            <div class="card bg-base-100 border">
                <div class="card-body space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="font-semibold">Create a Moment</div>
                        <div class="badge badge-outline badge-sm">Verified/Admin</div>
                    </div>

	                    <form wire:submit="create" class="space-y-3">
	                        <div>
	                            <x-input-label for="title" value="Title" />
	                            <x-text-input id="title" class="mt-1 block w-full input-sm" wire:model="title" />
	                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
	                        </div>

	                        <div>
	                            <x-input-label for="description" value="Description" />
	                            <textarea id="description" class="textarea textarea-bordered textarea-sm mt-1 block w-full" rows="3" wire:model="description"></textarea>
	                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
	                        </div>

	                        <div>
	                            <x-input-label for="cover_image" value="Cover image (optional)" />
	                            <input id="cover_image" type="file" class="file-input file-input-bordered file-input-sm w-full mt-1" wire:model="cover_image" />
	                            <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
	                        </div>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_public" />
                            <span class="text-sm">Public</span>
                        </label>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Create a Moment</div>
                    <div class="text-sm opacity-70 pt-2">
                        Moments are a legacy feature; creation is limited to verified accounts.
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Create a Moment</div>
                <div class="text-sm opacity-70 pt-2">
                    <a class="link link-hover" href="{{ route('login') }}" wire:navigate>Login</a> to browse and (if verified) create Moments.
                </div>
            </div>
        </div>
    @endauth

    @auth
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Your Moments</div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->moments as $moment)
                        <a class="card bg-base-200 card-hover" href="{{ route('moments.show', $moment) }}" wire:navigate>
                            <div class="card-body py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-semibold truncate">{{ $moment->title }}</div>
                                        @if ($moment->description)
                                            <div class="text-sm opacity-70 truncate">{{ $moment->description }}</div>
                                        @endif
                                    </div>
                                    <div class="text-sm opacity-60 shrink-0">
                                        {{ $moment->items_count }} posts{{ $moment->is_public ? '' : ' · Private' }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="opacity-70 text-sm">No moments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endauth

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Public Moments</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->publicMoments as $moment)
                    <a class="card bg-base-200 card-hover" href="{{ route('moments.show', $moment) }}" wire:navigate>
                        <div class="card-body py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate">{{ $moment->title }}</div>
                                    <div class="text-sm opacity-70 truncate">by &#64;{{ $moment->owner->username }}</div>
                                </div>
                                <div class="text-sm opacity-60 shrink-0">{{ $moment->items_count }} posts</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="opacity-70 text-sm">No public moments yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
