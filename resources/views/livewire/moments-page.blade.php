<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="text-xl font-semibold">Moments</div>
            <div class="text-sm opacity-70 pt-1">Curated collections of posts.</div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <div class="font-semibold">Create a Moment</div>
            <form wire:submit="create" class="space-y-3">
                <div>
                    <x-input-label for="title" value="Title" />
                    <x-text-input id="title" class="mt-1 block w-full" wire:model="title" />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" class="textarea textarea-bordered mt-1 block w-full" rows="3" wire:model="description"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
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

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Your Moments</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->moments as $moment)
                    <a class="card bg-base-200 border hover:border-base-300 transition" href="{{ route('moments.show', $moment) }}" wire:navigate>
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
</div>

