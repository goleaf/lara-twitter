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

            <div class="rounded-box border border-base-200 bg-base-100 hover:bg-base-200/50 hover:border-base-300 transition focus-within:ring-2 focus-within:ring-primary/20 {{ $isUnread ? 'ring-1 ring-primary/20' : '' }}">
                <div class="flex items-start justify-between gap-4 px-4 py-3">
                    <a class="flex items-start gap-3 min-w-0 flex-1" href="{{ route('messages.show', $conversation) }}" wire:navigate>
                        <div class="shrink-0">
                            @if ($conversation->is_group)
                                <div class="avatar-group -space-x-3">
                                    @forelse ($others->take(3) as $u)
                                        <div class="avatar">
                                            <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                                @if ($u->avatar_url)
                                                    <img src="{{ $u->avatar_url }}" alt="" />
                                                @else
                                                    <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                        {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="avatar placeholder">
                                            <div class="bg-base-200 text-base-content rounded-full w-9">
                                                <span class="text-xs font-semibold">G</span>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            @else
                                @php($u = $others->first())
                                <div class="avatar">
                                    <div class="w-10 rounded-full border border-base-200 bg-base-100">
                                        @if ($u?->avatar_url)
                                            <img src="{{ $u->avatar_url }}" alt="" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                                {{ mb_strtoupper(mb_substr($u?->name ?? 'C', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 min-w-0">
                                @if ($isUnread)
                                    <span class="badge badge-primary badge-sm">New</span>
                                @endif

                                @if ($meParticipant?->is_pinned)
                                    <span class="badge badge-outline badge-sm">Pinned</span>
                                @endif

                                <div class="font-semibold truncate min-w-0">
                                    @if ($conversation->is_group)
                                        {{ $conversation->title ?? 'Group' }}
                                    @else
                                        {{ $others->first()?->name ?? 'Conversation' }}
                                        <span class="opacity-60 font-normal">&#64;{{ $others->first()?->username }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-sm opacity-70 truncate">
                                {{ $last?->body ?? ($last?->attachments?->count() ? 'Attachment' : 'No messages yet') }}
                            </div>
                        </div>
                    </a>

                    <div class="shrink-0 text-right space-y-1">
                        <div class="text-xs opacity-60">
                            {{ $last?->created_at?->diffForHumans() }}
                        </div>
                        <button
                            type="button"
                            wire:click.prevent="togglePin({{ $conversation->id }})"
                            class="btn btn-ghost btn-xs"
                        >
                            {{ $meParticipant?->is_pinned ? 'Unpin' : 'Pin' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">No conversations.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
