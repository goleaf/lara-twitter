@php($me = auth()->user())
@php($myParticipant = $this->myParticipant)

<div class="max-w-2xl mx-auto space-y-4" wire:poll.5s="markRead">
    <div class="card bg-base-100 border">
        <div class="card-body">
            @php($others = $conversation->participants->pluck('user')->filter(fn ($u) => $u->id !== $me->id))

            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-xl font-semibold truncate">
                        @if ($conversation->is_group)
                            {{ $conversation->title ?? 'Group' }}
                        @else
                            {{ $others->first()?->name ?? 'Conversation' }}
                            <span class="opacity-60 font-normal">&#64;{{ $others->first()?->username }}</span>
                        @endif
                    </div>
                    <div class="text-sm opacity-70 truncate">
                        {{ $conversation->is_group ? $others->pluck('username')->map(fn ($u) => '@'.$u)->join(', ') : 'Direct message' }}
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
                    <button type="button" wire:click="declineRequest" class="btn btn-ghost btn-sm">Decline</button>
                    <button type="button" wire:click="acceptRequest" class="btn btn-primary btn-sm">Accept</button>
                </div>
            </div>
        </div>
    @endif

    @if ($conversation->is_group)
        <div class="card bg-base-100 border">
            <div class="card-body space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="font-semibold">Group</div>
                    <button type="button" wire:click="leaveGroup" class="btn btn-ghost btn-sm">Leave</button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($conversation->participants as $participant)
                        <a class="badge badge-outline" href="{{ route('profile.show', ['user' => $participant->user]) }}" wire:navigate>
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
                        <input class="input input-bordered w-full" placeholder="Group title" wire:model="groupTitle" />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Save</button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('groupTitle')" />

                    <form wire:submit="addMember" class="flex flex-col sm:flex-row gap-2">
                        <input class="input input-bordered w-full" placeholder="@username" wire:model="memberUsername" />
                        <button type="submit" class="btn btn-primary btn-sm shrink-0">Add</button>
                    </form>
                    <x-input-error class="mt-2" :messages="$errors->get('memberUsername')" />

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($conversation->participants as $participant)
                            @if ($participant->user_id !== $me->id)
                                <button type="button" class="badge badge-neutral" wire:click="removeMember({{ $participant->user_id }})">
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
            <div class="chat {{ $message->user_id === $me->id ? 'chat-end' : 'chat-start' }}">
                <div class="chat-header opacity-70 text-xs">
                    &#64;{{ $message->user->username }}
                    <time class="ms-1">{{ $message->created_at->format('H:i') }}</time>
                    @if ($message->user_id === $me->id && $this->canUnsend($message))
                        <button type="button" wire:click="unsend({{ $message->id }})" class="link link-hover ms-2">Unsend</button>
                    @endif
                </div>
                <div class="chat-bubble">
                    @if ($message->body)
                        <div class="whitespace-pre-wrap">{{ $message->body }}</div>
                    @endif

                    @if ($message->attachments->count())
                        <div class="pt-2 grid grid-cols-2 gap-2">
                            @foreach ($message->attachments as $attachment)
                                @php($url = \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->path))
                                @if (str_starts_with($attachment->mime_type, 'image/'))
                                    <img class="rounded-box border" src="{{ $url }}" alt="" loading="lazy" />
                                @elseif (str_starts_with($attachment->mime_type, 'video/'))
                                    <video class="rounded-box border w-full" controls>
                                        <source src="{{ $url }}" type="{{ $attachment->mime_type }}" />
                                    </video>
                                @elseif (str_starts_with($attachment->mime_type, 'audio/'))
                                    <audio class="w-full" controls>
                                        <source src="{{ $url }}" type="{{ $attachment->mime_type }}" />
                                    </audio>
                                @else
                                    <a class="link link-hover text-sm" href="{{ $url }}" target="_blank" rel="noreferrer">
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
                                class="badge {{ $mine ? 'badge-primary' : 'badge-ghost' }}"
                                wire:click="toggleReaction({{ $message->id }}, @js($emoji))"
                                @disabled($myParticipant?->is_request)
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
                            <div tabindex="0" class="dropdown-content bg-base-100 border rounded-box shadow p-2">
                                <div class="flex gap-1">
                                    @foreach ($this->reactionEmojis() as $emoji)
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-xs"
                                            wire:click="toggleReaction({{ $message->id }}, @js($emoji))"
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
                        class="textarea textarea-bordered w-full"
                        rows="3"
                        placeholder="Write a message..."
                        maxlength="10000"
                        @disabled($myParticipant?->is_request)
                    ></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div>
                    <input wire:model="attachments" type="file" multiple class="file-input file-input-bordered w-full" @disabled($myParticipant?->is_request) />
                    <x-input-error class="mt-2" :messages="$errors->get('attachments')" />
                    <x-input-error class="mt-2" :messages="$errors->get('attachments.*')" />
                    <div class="text-xs opacity-70 mt-1">Up to 4 attachments (images/video/audio/GIF), 10MB each.</div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm" @disabled($myParticipant?->is_request)>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
