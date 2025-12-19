<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @php($recipientCount = count($recipientUserIds))
    @php($isGroup = $recipientCount >= 2)

    <div class="card bg-base-100 border">
        <div class="card-body space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="text-xl font-semibold">New message</div>
                <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>Back</a>
            </div>

            <form wire:submit="addRecipient" class="flex flex-col sm:flex-row gap-2">
                <input
                    class="input input-bordered input-sm w-full"
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

            @if ($recipientCount)
                <div class="space-y-2">
                    @foreach ($this->recipients as $user)
                        <div class="flex items-center justify-between gap-3 rounded-box px-3 py-2 border border-base-200 bg-base-200/40 focus-within:ring-2 focus-within:ring-primary/20">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="avatar shrink-0">
                                    <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                        @if ($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <div class="font-semibold truncate">
                                        {{ $user->name }}
                                        @if ($user->is_verified)
                                            <x-verified-icon class="ms-1 align-middle" />
                                        @endif
                                    </div>
                                    <div class="text-xs opacity-60 truncate">&#64;{{ $user->username }}</div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-ghost btn-xs" wire:click="removeRecipient({{ $user->id }})">
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body space-y-3">
            <form wire:submit="create" class="space-y-3">
                @if ($isGroup)
                    <div>
                        <x-input-label for="title" value="Group title (optional)" />
                        <x-text-input id="title" class="mt-1 block w-full input-sm" wire:model="title" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>
                @elseif ($recipientCount === 1)
                    <div class="text-sm opacity-70">
                        This will start a direct message.
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm" @disabled(count($recipientUserIds) === 0)>
                        Start conversation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
