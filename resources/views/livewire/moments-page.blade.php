<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
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

                        <label class="flex items-start justify-between gap-4 rounded-box border border-base-200 bg-base-200/40 px-4 py-3 cursor-pointer">
                            <div class="min-w-0">
                                <div class="font-medium">Public</div>
                                <div class="text-sm opacity-70">Show this Moment in the public directory.</div>
                            </div>
                            <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="is_public" />
                        </label>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="create">Create</button>
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
                <div class="flex items-center justify-between gap-3">
                    <div class="font-semibold">Your Moments</div>
                    <span class="badge badge-outline badge-sm">{{ $this->moments->count() }}</span>
                </div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->moments as $moment)
                        @php($cover = $moment->coverUrl())
                        <a class="flex items-start justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('moments.show', $moment) }}" wire:navigate>
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="shrink-0">
                                    <div class="w-14 h-14 rounded-box border border-base-200 bg-base-200 overflow-hidden">
                                        @if ($cover)
                                            <img class="w-full h-full object-cover" src="{{ $cover }}" alt="{{ $moment->title }} cover image" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">M</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-start gap-2 flex-wrap">
                                        <div class="font-semibold truncate">{{ $moment->title }}</div>
                                        @if (! $moment->is_public)
                                            <span class="badge badge-outline badge-sm">Private</span>
                                        @endif
                                    </div>
                                    <div class="text-xs opacity-60 truncate">{{ $moment->items_count }} posts</div>
                                    @if ($moment->description)
                                        <div class="text-sm opacity-70 truncate">{{ $moment->description }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-sm opacity-60 shrink-0">View</div>
                        </a>
                    @empty
                        <x-empty-state>
                            No moments yet.
                        </x-empty-state>
                    @endforelse
                </div>
            </div>
        </div>
    @endauth

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Public Moments</div>
                <span class="badge badge-outline badge-sm">{{ $this->publicMoments->count() }}</span>
            </div>
            <div class="space-y-2 pt-2">
                @forelse ($this->publicMoments as $moment)
                    @php($cover = $moment->coverUrl())
                    <a class="flex items-start justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('moments.show', $moment) }}" wire:navigate>
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="shrink-0">
                                <div class="w-14 h-14 rounded-box border border-base-200 bg-base-200 overflow-hidden">
                                    @if ($cover)
                                        <img class="w-full h-full object-cover" src="{{ $cover }}" alt="{{ $moment->title }} cover image" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">M</div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $moment->title }}</div>
                                <div class="flex items-center gap-2 text-xs opacity-60 min-w-0">
                                    <div class="avatar shrink-0">
                                        <div class="w-6 rounded-full border border-base-200 bg-base-100">
                                            @if ($moment->owner->avatar_url)
                                                <img src="{{ $moment->owner->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                            @else
                                                <div class="bg-base-200 grid place-items-center h-full w-full text-[10px] font-semibold">
                                                    {{ mb_strtoupper(mb_substr($moment->owner->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="truncate min-w-0">
                                        by &#64;{{ $moment->owner->username }} · {{ $moment->items_count }} posts
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">View</div>
                    </a>
                @empty
                    <x-empty-state>
                        No public moments yet.
                    </x-empty-state>
                @endforelse
            </div>
        </div>
    </div>
</div>
