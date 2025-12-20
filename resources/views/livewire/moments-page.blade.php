<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border hero-card moments-hero">
        <div class="hero-edge" aria-hidden="true"></div>
        <div class="card-body gap-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="text-2xl font-semibold">Moments</div>
                        <span class="badge badge-ghost badge-sm">Legacy</span>
                    </div>
                    <div class="text-sm opacity-70 max-w-xl">
                        Curated collections of posts with a title, description, and cover image.
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="guest-hero-chip">Curated stories</span>
                    <span class="guest-hero-chip">Cover art</span>
                    <span class="guest-hero-chip">Threaded posts</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a class="btn btn-primary btn-sm" href="#public-moments">Browse public</a>
                @auth
                    <a class="btn btn-outline btn-sm" href="#your-moments">Your moments</a>
                    <a class="btn btn-ghost btn-sm" href="#create-moment">Create moment</a>
                @else
                    <a class="btn btn-outline btn-sm" href="{{ route('login') }}" wire:navigate>Log in to create</a>
                @endauth
            </div>

            <div class="flex flex-wrap gap-2 text-xs">
                <span class="badge badge-outline badge-sm">Public {{ $this->publicMoments->total() }}</span>
                @auth
                    <span class="badge badge-outline badge-sm">Yours {{ $this->moments->total() }}</span>
                @endauth
            </div>
        </div>
    </div>

    @auth
        @if ($this->canCreate)
            <div id="create-moment" class="card bg-base-100 border">
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

                        <x-choice-card>
                            <div class="min-w-0">
                                <div class="font-medium">Public</div>
                                <div class="text-sm opacity-70">Show this Moment in the public directory.</div>
                            </div>
                            <input type="checkbox" class="toggle toggle-sm mt-1" wire:model="is_public" wire:loading.attr="disabled" wire:target="create" />
                        </x-choice-card>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="create">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div id="create-moment" class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Create a Moment</div>
                    <div class="text-sm opacity-70 pt-2">
                        Moments are a legacy feature; creation is limited to verified accounts.
                    </div>
                </div>
            </div>
        @endif
    @else
        <div id="create-moment" class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Create a Moment</div>
                <div class="text-sm opacity-70 pt-2">
                    <a class="link link-hover" href="{{ route('login') }}" wire:navigate>Login</a> to browse and (if verified) create Moments.
                </div>
            </div>
        </div>
    @endauth

    @auth
        <div id="your-moments" class="card bg-base-100 border">
            <div class="card-body">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-semibold">Your Moments</div>
                    <span class="badge badge-outline badge-sm">{{ $this->moments->total() }}</span>
                </div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->moments as $moment)
                        @php($cover = $moment->coverUrl())
                        <x-list-row href="{{ route('moments.show', $moment) }}" wire:navigate class="items-start" wire:key="moment-owned-{{ $moment->id }}">
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
                        </x-list-row>
                    @empty
                        <x-empty-state>
                            No moments yet.
                        </x-empty-state>
                    @endforelse
                </div>

                @if ($this->moments->hasPages())
                    <div class="pt-3">
                        {{ $this->moments->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endauth

    <div id="public-moments" class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="font-semibold">Public Moments</div>
                <span class="badge badge-outline badge-sm">{{ $this->publicMoments->total() }}</span>
            </div>
            <div class="space-y-2 pt-2">
                @forelse ($this->publicMoments as $moment)
                    @php($cover = $moment->coverUrl())
                    <x-list-row href="{{ route('moments.show', $moment) }}" wire:navigate class="items-start" wire:key="moment-public-{{ $moment->id }}">
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
                    </x-list-row>
                @empty
                    <x-empty-state>
                        No public moments yet.
                    </x-empty-state>
                @endforelse
            </div>

            @if ($this->publicMoments->hasPages())
                <div class="pt-3">
                    {{ $this->publicMoments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
