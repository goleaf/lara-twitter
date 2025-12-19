<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Messages</div>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($this->conversations as $conversation)
            @php($last = $conversation->messages->first())
            @php($others = $conversation->participants->pluck('user')->filter(fn ($u) => $u->id !== auth()->id()))

            <a class="card bg-base-100 border hover:border-base-300 transition" href="{{ route('messages.show', $conversation) }}" wire:navigate>
                <div class="card-body py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">
                                @if ($conversation->is_group)
                                    {{ $conversation->title ?? 'Group' }}
                                @else
                                    {{ $others->first()?->name ?? 'Conversation' }}
                                    <span class="opacity-60 font-normal">&#64;{{ $others->first()?->username }}</span>
                                @endif
                            </div>
                            <div class="text-sm opacity-70 truncate">
                                {{ $last?->body ?? (optional($last)->attachments->count() ? 'Attachment' : 'No messages yet') }}
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">
                            {{ $last?->created_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">No conversations yet.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
