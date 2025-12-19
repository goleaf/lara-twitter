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

            @if ($moment->description)
                <div>{{ $moment->description }}</div>
            @endif

            @auth
                @if (auth()->id() === $moment->owner_id)
                    <div class="divider">Add post</div>

                    <form wire:submit="addPost" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered w-full" placeholder="Post ID (e.g. 123)" wire:model="post_id" />
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
                        <div class="flex justify-end">
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

