<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-semibold">New message</div>
                <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>Back</a>
            </div>

            <form wire:submit="addRecipient" class="flex flex-col sm:flex-row gap-2">
                <input
                    class="input input-bordered w-full"
                    placeholder="@username"
                    wire:model="recipientUsername"
                />
                <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
            </form>
            <x-input-error class="mt-2" :messages="$errors->get('recipientUsername')" />

            <div class="flex items-center justify-between">
                <div class="text-sm opacity-70">
                    {{ count($recipientUserIds) + 1 }}/50 participants
                </div>
                <div class="text-sm opacity-70">
                    Add 1 person for a DM, 2+ for a group.
                </div>
            </div>

            @if (count($recipientUserIds))
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->recipients as $user)
                        <button type="button" class="badge badge-neutral" wire:click="removeRecipient({{ $user->id }})">
                            Remove &#64;{{ $user->username }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <form wire:submit="create" class="space-y-3">
                <div>
                    <x-input-label for="title" value="Group title (optional)" />
                    <x-text-input id="title" class="mt-1 block w-full" wire:model="title" />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm" @disabled(count($recipientUserIds) === 0)>
                        Start conversation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

