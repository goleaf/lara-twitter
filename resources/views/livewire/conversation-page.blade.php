@php($me = auth()->user())
@php($myParticipant = $this->myParticipant)

<div class="max-w-2xl lg:max-w-3xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body relative">
            <span
                class="absolute top-0 left-0 w-1 h-1 opacity-0"
                aria-hidden="true"
                wire:poll.visible.5s="markRead"
                wire:loading.class="hidden"
                wire:target="attachments"
            ></span>
            @php($others = $conversation->participants->pluck('user')->filter(fn ($u) => $u && $u->id !== $me->id)->values())
            @php($other = $others->first())
            @php($avatarUsers = $others->take(3))

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="shrink-0 pt-0.5">
                        @if ($conversation->is_group)
                            <div class="avatar-group -space-x-3">
                                @foreach ($avatarUsers as $u)
                                    <div class="avatar" wire:key="group-avatar-{{ $conversation->id }}-{{ $u->id }}">
                                        <div class="w-10 rounded-full border border-base-200 bg-base-100">
                                            @if ($u->avatar_url)
                                                <img src="{{ $u->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                            @else
                                                <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                                    {{ mb_strtoupper(mb_substr($u->name ?? $u->username ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                @if ($others->count() > $avatarUsers->count())
                                    <div class="avatar placeholder">
                                        <div class="w-10 rounded-full border border-base-200 bg-base-200 text-xs font-semibold">
                                            +{{ $others->count() - $avatarUsers->count() }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="avatar">
                                <div class="w-12 rounded-full border border-base-200 bg-base-100">
                                    @if ($other?->avatar_url)
                                        <img src="{{ $other->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-lg font-semibold">
                                            {{ mb_strtoupper(mb_substr($other?->name ?? $other?->username ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="text-xl font-semibold truncate">
                            @if ($conversation->is_group)
                                {{ $conversation->title ?? 'Group' }}
                            @else
                                {{ $other?->name ?? 'Conversation' }}
                                @if ($other?->username)
                                    <span class="opacity-60 font-normal">&#64;{{ $other->username }}</span>
                                @endif
                            @endif
                        </div>
                        <div class="text-sm opacity-70 truncate">
                            {{ $conversation->is_group ? $others->pluck('username')->filter()->map(fn ($u) => '@'.$u)->join(', ') : 'Direct message' }}
                        </div>
                    </div>
                </div>

                <a class="btn btn-ghost btn-sm" href="{{ route('messages.index') }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    @if ($myParticipant?->is_request)
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Message request</div>
                <div class="text-sm opacity-70">Accept to reply and move this conversation to your inbox.</div>

                <div class="pt-3 flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="declineRequest"
                        class="btn btn-ghost btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="acceptRequest,declineRequest"
                    >
                        Decline
                    </button>
                    <button
                        type="button"
                        wire:click="acceptRequest"
                        class="btn btn-primary btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="acceptRequest,declineRequest"
                    >
                        Accept
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($conversation->is_group)
        <div class="card bg-base-100 border">
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="font-semibold">Group</div>
                    <button
                        type="button"
                        wire:click="leaveGroup"
                        class="btn btn-ghost btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="leaveGroup"
                    >
                        Leave
                    </button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($conversation->participants as $participant)
                        <a class="badge badge-outline" href="{{ route('profile.show', ['user' => $participant->user]) }}" wire:navigate wire:key="group-participant-{{ $conversation->id }}-{{ $participant->id }}">
                            &#64;{{ $participant->user->username }}
                            @if ($participant->role === 'admin')
                                <span class="opacity-60 ms-1">(admin)</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if ($this->isGroupAdmin)
                    <div class="divider">Manage</div>

                    <form wire:submit="updateGroupTitle" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered input-sm w-full" placeholder="Group title" wire:model="groupTitle" />
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm shrink-0"
                            wire:loading.attr="disabled"
                            wire:target="updateGroupTitle"
                        >
                            Save
                        </button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('groupTitle')" />

                    <form wire:submit="addMember" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered input-sm w-full" placeholder="@username" wire:model="memberUsername" />
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm shrink-0"
                            wire:loading.attr="disabled"
                            wire:target="addMember"
                        >
                            Add
                        </button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('memberUsername')" />

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($conversation->participants as $participant)
                            @if ($participant->user_id !== $me->id)
                                <button
                                    type="button"
                                    class="badge badge-sm badge-neutral"
                                    wire:click="removeMember({{ $participant->user_id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="removeMember({{ $participant->user_id }})"
                                    wire:key="group-remove-{{ $conversation->id }}-{{ $participant->id }}"
                                >
                                    Remove &#64;{{ $participant->user->username }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="space-y-2">
        @foreach ($this->messages as $message)
            <div class="chat {{ $message->user_id === $me->id ? 'chat-end' : 'chat-start' }}" wire:key="message-{{ $message->id }}">
                <div class="chat-header opacity-70 text-xs">
                    &#64;{{ $message->user->username }}
                    <time class="ms-1">{{ $message->created_at->format('H:i') }}</time>
                    @if ($message->user_id === $me->id && $this->canUnsend($message))
                        <button
                            type="button"
                            wire:click="unsend({{ $message->id }})"
                            class="link link-hover ms-2"
                            wire:loading.attr="disabled"
                            wire:target="unsend({{ $message->id }})"
                        >
                            Unsend
                        </button>
                    @endif
                </div>
                <div class="chat-bubble {{ $message->user_id === $me->id ? 'chat-bubble-primary' : '' }}">
                    @if ($message->body)
                        <div class="whitespace-pre-wrap">{{ $message->body }}</div>
                    @endif

                    @php($attachmentsCount = $message->attachments->count())
                    @if ($attachmentsCount)
                        @php($gridClass = $attachmentsCount === 1 ? 'grid-cols-1' : 'grid-cols-2')

                        <div class="pt-2 grid {{ $gridClass }} gap-2">
                            @foreach ($message->attachments as $attachment)
                                @php($url = \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->path))
                                @php($isWide = $attachmentsCount === 1 || ($attachmentsCount === 3 && $loop->last))
                                @php($spanClass = $attachmentsCount === 3 && $loop->last ? 'col-span-2' : '')
                                @php($ratio = $isWide ? '16 / 9' : '1 / 1')

                                @if (str_starts_with($attachment->mime_type, 'image/'))
                                    <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200 {{ $spanClass }}" style="aspect-ratio: {{ $ratio }};" wire:key="message-attachment-{{ $message->id }}-{{ $attachment->id }}">
                                        <img class="h-full w-full object-cover" src="{{ $url }}" alt="" loading="lazy" decoding="async" />
                                    </div>
                                @elseif (str_starts_with($attachment->mime_type, 'video/'))
                                    <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200 {{ $spanClass }}" style="aspect-ratio: {{ $ratio }};" wire:key="message-attachment-{{ $message->id }}-{{ $attachment->id }}">
                                        <video class="h-full w-full" controls preload="metadata">
                                            <source src="{{ $url }}" type="{{ $attachment->mime_type }}" />
                                        </video>
                                    </div>
                                @elseif (str_starts_with($attachment->mime_type, 'audio/'))
                                    <div class="rounded-box border border-base-200 bg-base-200/40 p-2 {{ $spanClass }}" wire:key="message-attachment-{{ $message->id }}-{{ $attachment->id }}">
                                        <audio class="w-full" controls>
                                            <source src="{{ $url }}" type="{{ $attachment->mime_type }}" />
                                        </audio>
                                    </div>
                                @else
                                    <a class="btn btn-ghost btn-sm justify-start w-full {{ $spanClass }}" href="{{ $url }}" target="_blank" rel="noopener noreferrer" wire:key="message-attachment-{{ $message->id }}-{{ $attachment->id }}">
                                        {{ basename($attachment->path) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-2 flex justify-end">
                        <livewire:report-button :reportable-type="\App\Models\Message::class" :reportable-id="$message->id" label="Report" :key="'report-message-'.$message->id" />
                    </div>
                </div>

                @php($reactions = $message->reactions->groupBy('emoji'))
                @if ($reactions->count())
                    <div class="pt-1 flex flex-wrap gap-1">
                        @foreach ($reactions as $emoji => $items)
                            @php($mine = $items->contains('user_id', $me->id))
                            <button
                                type="button"
                                class="badge badge-sm {{ $mine ? 'badge-primary' : 'badge-ghost' }}"
                                wire:click="toggleReaction({{ $message->id }}, @js($emoji))"
                                wire:loading.attr="disabled"
                                wire:target="toggleReaction({{ $message->id }}, @js($emoji))"
                                @disabled($myParticipant?->is_request)
                                wire:key="message-reaction-{{ $message->id }}-{{ md5($emoji) }}"
                            >
                                {{ $emoji }} {{ $items->count() }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @if (! $myParticipant?->is_request)
                    <div class="pt-1">
                        <div class="dropdown {{ $message->user_id === $me->id ? 'dropdown-end' : 'dropdown-start' }}">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-xs">React</div>
                            <div tabindex="0" class="dropdown-content z-[1] bg-base-100/90 supports-[backdrop-filter]:bg-base-100/70 backdrop-blur border border-base-200 rounded-box shadow-lg mt-2 p-2">
                                <div class="flex gap-1">
                                    @foreach ($this->reactionEmojis() as $emoji)
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-xs"
                                            wire:click="toggleReaction({{ $message->id }}, @js($emoji))"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleReaction({{ $message->id }}, @js($emoji))"
                                            wire:key="message-reaction-picker-{{ $message->id }}-{{ md5($emoji) }}"
                                        >
                                            {{ $emoji }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($loop->first && ($label = $this->readReceiptLabelFor($message)))
                    <div class="chat-footer opacity-60 text-xs">{{ $label }}</div>
                @endif
            </div>
        @endforeach

        <div class="pt-2">
            {{ $this->messages->links() }}
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            @if ($this->typingUsernames->count())
                <div class="text-sm opacity-70 pb-2">
                    {{ $this->typingUsernames->map(fn ($u) => '@'.$u)->join(', ') }} typing…
                </div>
            @endif

            <form wire:submit="send" class="space-y-3">
                <div>
                    <textarea
                        wire:model="body"
                        wire:keydown.throttle.800ms="typing"
                        class="textarea textarea-bordered textarea-sm w-full"
                        rows="3"
                        placeholder="Write a message..."
                        maxlength="10000"
                        @disabled($myParticipant?->is_request)
                    ></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div>
                    <input
                        wire:model="attachments"
                        type="file"
                        multiple
                        class="file-input file-input-bordered file-input-sm w-full"
                        wire:loading.attr="disabled"
                        wire:target="attachments,send"
                        @disabled($myParticipant?->is_request)
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('attachments')" />
                    <x-input-error class="mt-2" :messages="$errors->get('attachments.*')" />
                    <div class="text-xs opacity-70 mt-1">Up to 4 attachments (images/video/audio/GIF), 10MB each.</div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm"
                        wire:loading.attr="disabled"
                        wire:target="send,attachments"
                        @disabled($myParticipant?->is_request)
                    >
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
