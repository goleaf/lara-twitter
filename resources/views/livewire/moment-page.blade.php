<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                    <a class="avatar shrink-0" href="{{ route('profile.show', ['user' => $moment->owner]) }}" wire:navigate>
                        <div class="w-12 rounded-full border border-base-200 bg-base-100">
                            @if ($moment->owner->avatar_url)
                                <img src="{{ $moment->owner->avatar_url }}" alt="" loading="lazy" decoding="async" />
                            @else
                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr($moment->owner->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="min-w-0">
                        <div class="flex items-start gap-2 flex-wrap">
                            <div class="text-xl font-semibold truncate">{{ $moment->title }}</div>
                            @if (! $moment->is_public)
                                <span class="badge badge-outline badge-sm">Private</span>
                            @endif
                        </div>
                        <div class="text-sm opacity-70 truncate">
                            by <a class="link link-hover" href="{{ route('profile.show', ['user' => $moment->owner]) }}" wire:navigate>&#64;{{ $moment->owner->username }}</a>
                        </div>
                    </div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('moments.index') }}" wire:navigate>Back</a>
            </div>

            @php($coverUrl = $moment->coverUrl())
            @if ($coverUrl)
                <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200" style="aspect-ratio: 16 / 9;">
                    <img class="h-full w-full object-cover" src="{{ $coverUrl }}" alt="Moment cover image" loading="lazy" decoding="async" />
                </div>
            @endif

            @if ($moment->description)
                <div class="text-sm opacity-80 leading-relaxed">{{ $moment->description }}</div>
            @endif

            @auth
                @if (auth()->id() === $moment->owner_id)
                    <div class="divider">Edit moment</div>

                    <form wire:submit="updateMoment" class="space-y-3">
                        <div>
                            <x-input-label for="moment_title" value="Title" />
                            <x-text-input id="moment_title" class="mt-1 block w-full input-sm" wire:model="title" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="moment_description" value="Description" />
                            <textarea id="moment_description" class="textarea textarea-bordered textarea-sm mt-1 block w-full" rows="3" wire:model="description"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="moment_cover_image" value="Cover image (optional)" />
                            <input id="moment_cover_image" type="file" class="file-input file-input-bordered file-input-sm w-full mt-1" wire:model="cover_image" />
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
                            <button type="submit" class="btn btn-outline btn-sm" wire:loading.attr="disabled" wire:target="updateMoment">Save</button>
                        </div>
                    </form>

                    <div class="divider">Add post</div>

                    <form wire:submit="addPost" class="space-y-2">
                        <div class="join w-full">
                            <input class="input input-bordered input-sm join-item w-full" placeholder="Post ID or URL (e.g. 123 or /posts/123)" wire:model="post_id" wire:loading.attr="disabled" wire:target="addPost" />
                            <button type="submit" class="btn btn-primary btn-sm join-item" wire:loading.attr="disabled" wire:target="addPost">Add</button>
                        </div>

                        <textarea
                            class="textarea textarea-bordered textarea-sm w-full"
                            rows="2"
                            placeholder="Context (optional)"
                            wire:model="caption"
                            wire:loading.attr="disabled"
                            wire:target="addPost"
                        ></textarea>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('post_id')" />
                    <x-input-error class="mt-2" :messages="$errors->get('caption')" />
                @endif
            @endauth
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($this->items as $item)
            <div class="space-y-2">
                @if ($this->editing_item_id === $item->id)
                    <div class="card bg-base-100 border">
                        <div class="card-body py-4 space-y-2">
                            <div class="font-semibold">Caption</div>

                            <form wire:submit="saveCaption" class="space-y-2">
                                <textarea class="textarea textarea-bordered textarea-sm w-full" rows="3" wire:model="editing_caption"></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('editing_caption')" />

                                <div class="flex justify-end gap-2">
                                    <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelEditingCaption">Cancel</button>
                                    <button type="submit" class="btn btn-outline btn-sm">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif ($item->caption)
                    <div class="rounded-box bg-base-100 border px-4 py-3 text-sm opacity-80">
                        {{ $item->caption }}
                    </div>
                @endif

                <livewire:post-card :post="$item->post" :key="'moment-post-'.$item->post->id" />

                @auth
                    @if (auth()->id() === $moment->owner_id)
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="btn btn-ghost btn-xs" wire:click="startEditingCaption({{ $item->id }})">
                                {{ $item->caption ? 'Edit caption' : 'Add caption' }}
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs" wire:click="moveItemUp({{ $item->id }})">Up</button>
                            <button type="button" class="btn btn-ghost btn-xs" wire:click="moveItemDown({{ $item->id }})">Down</button>
                            <button type="button" class="btn btn-ghost btn-xs" wire:click="removeItem({{ $item->id }})">Remove</button>
                        </div>
                    @endif
                @endauth
            </div>
        @empty
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">No posts in this moment yet.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
