@php($me = auth()->user())
@php($isHost = $me && $me->id === $space->host_user_id)
@php($participant = $this->myParticipant())
@php($isModerator = $me && ($isHost || ($participant && $participant->left_at === null && $participant->role === 'cohost')))
@php($myRequest = $this->mySpeakerRequest())

<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xl font-semibold">{{ $space->title }}</div>
                    <div class="text-sm opacity-70">Host: &#64;{{ $space->host->username }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <livewire:report-button :reportable-type="\App\Models\Space::class" :reportable-id="$space->id" label="Report" :key="'report-space-'.$space->id" />
                    <a class="btn btn-ghost btn-sm" href="{{ route('spaces.index') }}" wire:navigate>Back</a>
                </div>
            </div>

            @if ($space->description)
                <div class="pt-1">{{ $space->description }}</div>
            @endif

            <div class="flex flex-wrap gap-2 text-sm opacity-70">
                @if ($space->isEnded())
                    <span class="badge">Ended</span>
                @elseif ($space->isLive())
                    <span class="badge badge-primary">Live</span>
                @else
                    <span class="badge">Not started</span>
                @endif

                @if ($space->scheduled_for)
                    <span>Scheduled {{ $space->scheduled_for->diffForHumans() }}</span>
                @endif

                @if ($space->recording_enabled)
                    @if ($space->recording_available_until)
                        <span>Recording available until {{ $space->recording_available_until->toDateString() }}</span>
                    @else
                        <span>Recording enabled</span>
                    @endif
                @endif

                <span>{{ $space->participants_count }} participants</span>
            </div>

            @if ($space->pinnedPost)
                <div class="pt-3 space-y-2">
                    <div class="text-sm font-semibold opacity-70">Pinned post</div>
                    <livewire:post-card :post="$space->pinnedPost" :key="'space-pin-'.$space->pinnedPost->id" />
                </div>
            @endif

            @if ($isModerator)
                <div class="pt-3">
                    <form wire:submit="pinPost" class="flex flex-wrap items-end gap-2">
                        <div class="grow min-w-[14rem]">
                            <x-input-label for="pinned_post_id" value="Pin a post (ID or URL)" />
                            <x-text-input id="pinned_post_id" class="mt-1 block w-full" wire:model="pinned_post_id" />
                            <x-input-error class="mt-2" :messages="$errors->get('pinned_post_id')" />
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm">Pin</button>
                        @if ($space->pinned_post_id)
                            <button type="button" class="btn btn-ghost btn-sm" wire:click="unpinPost">Unpin</button>
                        @endif
                    </form>
                </div>
            @endif

            <div class="alert mt-2">
                <div>
                    <div class="font-semibold">Audio not implemented</div>
                    <div class="text-sm opacity-70">This is a MVP placeholder for Spaces. To add live audio we need WebRTC signaling + TURN/STUN or a provider (Twilio/Agora).</div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                @auth
                    @if (!$participant || $participant->left_at)
                        <button class="btn btn-primary btn-sm" wire:click="join">Join</button>
                    @else
                        <button class="btn btn-outline btn-sm" wire:click="leave">Leave</button>
                    @endif

                    @if ($isHost)
                        @if (!$space->isLive() && !$space->isEnded())
                            <button class="btn btn-outline btn-sm" wire:click="start">Start</button>
                        @endif
                        @if (!$space->isEnded())
                            <button class="btn btn-outline btn-sm" wire:click="end">End</button>
                        @endif
                    @endif
                @else
                    <a class="btn btn-primary btn-sm" href="{{ route('login') }}" wire:navigate>Login to join</a>
                @endauth
            </div>

            @auth
                @if ($participant && $participant->left_at === null && $participant->role === 'listener' && ! $space->isEnded())
                    <div class="flex items-center gap-2 pt-2">
                        <button class="btn btn-outline btn-sm" wire:click="requestToSpeak">Request to speak</button>
                        @if ($myRequest?->status === \App\Models\SpaceSpeakerRequest::STATUS_PENDING)
                            <div class="text-sm opacity-70">Request pending</div>
                        @elseif ($myRequest?->status === \App\Models\SpaceSpeakerRequest::STATUS_DENIED)
                            <div class="text-sm opacity-70">Request denied</div>
                        @endif
                    </div>
                @endif
            @endauth
        </div>
    </div>

    @if ($isModerator)
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Speaker requests</div>
                <div class="space-y-2 pt-2">
                    @forelse ($this->pendingSpeakerRequests as $req)
                        <div class="flex items-center justify-between gap-3">
                            <a class="link link-hover" href="{{ route('profile.show', ['user' => $req->user]) }}" wire:navigate>
                                &#64;{{ $req->user->username }}
                            </a>
                            <div class="flex items-center gap-2">
                                <button class="btn btn-outline btn-xs" wire:click="decideSpeakerRequest({{ $req->id }}, 'approve')">Approve</button>
                                <button class="btn btn-outline btn-xs" wire:click="decideSpeakerRequest({{ $req->id }}, 'deny')">Deny</button>
                            </div>
                        </div>
                    @empty
                        <div class="opacity-70 text-sm">No pending requests.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Participants</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->participants as $p)
                    <div class="flex items-center justify-between gap-3">
                        <a class="link link-hover" href="{{ route('profile.show', ['user' => $p->user]) }}" wire:navigate>
                            &#64;{{ $p->user->username }}
                        </a>
                        <div class="flex items-center gap-2">
                            <div class="text-sm opacity-70">
                                {{ ucfirst($p->role) }}
                            </div>

                            @if ($isModerator && $p->user_id !== $space->host_user_id)
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">Manage</div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-44 border">
                                        <li><button type="button" wire:click="setParticipantRole({{ $p->id }}, 'listener')">Set listener</button></li>
                                        <li><button type="button" wire:click="setParticipantRole({{ $p->id }}, 'speaker')">Set speaker</button></li>
                                        <li><button type="button" wire:click="setParticipantRole({{ $p->id }}, 'cohost')">Set co-host</button></li>
                                        <li><button type="button" wire:click="removeParticipant({{ $p->id }})">Remove</button></li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="opacity-70 text-sm">No one joined yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
