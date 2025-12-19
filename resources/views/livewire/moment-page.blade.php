<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xl font-semibold truncate">{{ $moment->title }}</div>
                    <div class="text-sm opacity-70">by &#64;{{ $moment->owner->username }}{{ $moment->is_public ? '' : ' · Private' }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('moments.index') }}" wire:navigate>Back</a>
            </div>

            @if ($moment->coverUrl())
                <img class="rounded-box border w-full max-h-64 object-cover" src="{{ $moment->coverUrl() }}" alt="Moment cover image" />
            @endif

            @if ($moment->description)
                <div>{{ $moment->description }}</div>
            @endif

            @auth
                @if (auth()->id() === $moment->owner_id)
                    <div class="divider">Edit moment</div>

                    <form wire:submit="updateMoment" class="space-y-3">
                        <div>
                            <x-input-label for="moment_title" value="Title" />
                            <x-text-input id="moment_title" class="mt-1 block w-full" wire:model="title" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="moment_description" value="Description" />
                            <textarea id="moment_description" class="textarea textarea-bordered mt-1 block w-full" rows="3" wire:model="description"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="moment_cover_image" value="Cover image (optional)" />
                            <input id="moment_cover_image" type="file" class="file-input file-input-bordered w-full mt-1" wire:model="cover_image" />
                            <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                        </div>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_public" />
                            <span class="text-sm">Public</span>
                        </label>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-outline btn-sm">Save</button>
                        </div>
                    </form>

                    <div class="divider">Add post</div>

                    <form wire:submit="addPost" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered w-full" placeholder="Post ID or URL (e.g. 123 or /posts/123)" wire:model="post_id" />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('post_id')" />
                @endif
            @endauth
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($this->items as $item)
            <div class="space-y-2">
                <livewire:post-card :post="$item->post" :key="'moment-post-'.$item->post->id" />

                @auth
                    @if (auth()->id() === $moment->owner_id)
                        <div class="flex items-center justify-end gap-2">
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
