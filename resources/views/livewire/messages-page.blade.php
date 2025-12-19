<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Messages</div>
                <a class="btn btn-primary btn-sm" href="{{ route('messages.compose') }}" wire:navigate>New</a>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <button type="button" class="tab {{ $tab === 'inbox' ? 'tab-active' : '' }}" wire:click="$set('tab', 'inbox')">
                    Inbox
                </button>
                <button type="button" class="tab {{ $tab === 'requests' ? 'tab-active' : '' }}" wire:click="$set('tab', 'requests')">
                    Requests
                </button>
            </div>

            <div class="mt-4">
                <input
                    wire:model.live="q"
                    type="search"
                    placeholder="Search messages…"
                    class="input input-bordered input-sm w-full"
                />
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($this->conversations as $conversation)
            @php($last = $conversation->messages->first())
            @php($others = $conversation->participants->pluck('user')->filter(fn ($u) => $u->id !== auth()->id()))
            @php($meParticipant = $conversation->participants->firstWhere('user_id', auth()->id()))
            @php($isUnread = $last && $last->user_id !== auth()->id() && (! $meParticipant?->last_read_at || $meParticipant->last_read_at->lt($last->created_at)))

            <a class="card bg-base-100 card-hover" href="{{ route('messages.show', $conversation) }}" wire:navigate>
                <div class="card-body py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">
                                @if ($isUnread)
                                    <span class="badge badge-primary badge-sm">New</span>
                                @endif

                                @if ($meParticipant?->is_pinned)
                                    <span class="badge badge-neutral badge-sm">Pinned</span>
                                @endif

                                @if ($conversation->is_group)
                                    {{ $conversation->title ?? 'Group' }}
                                @else
                                    {{ $others->first()?->name ?? 'Conversation' }}
                                    <span class="opacity-60 font-normal">&#64;{{ $others->first()?->username }}</span>
                                @endif
                            </div>
                            <div class="text-sm opacity-70 truncate">
                                {{ $last?->body ?? ($last?->attachments?->count() ? 'Attachment' : 'No messages yet') }}
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">
                            {{ $last?->created_at?->diffForHumans() }}
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button
                            type="button"
                            wire:click.prevent="togglePin({{ $conversation->id }})"
                            class="btn btn-ghost btn-xs"
                        >
                            {{ $meParticipant?->is_pinned ? 'Unpin' : 'Pin' }}
                        </button>
                    </div>
                </div>
            </a>
        @empty
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">No conversations.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
