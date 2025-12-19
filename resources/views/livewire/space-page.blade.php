@php($me = auth()->user())
@php($isHost = $me && $me->id === $space->host_user_id)
@php($participant = $this->myParticipant())

<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xl font-semibold">{{ $space->title }}</div>
                    <div class="text-sm opacity-70">Host: &#64;{{ $space->host->username }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('spaces.index') }}" wire:navigate>Back</a>
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

                <span>{{ $space->participants_count }} participants</span>
            </div>

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
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Participants</div>
            <div class="space-y-2 pt-2">
                @forelse ($this->participants as $p)
                    <div class="flex items-center justify-between">
                        <a class="link link-hover" href="{{ route('profile.show', ['user' => $p->user]) }}" wire:navigate>
                            &#64;{{ $p->user->username }}
                        </a>
                        <div class="text-sm opacity-70">
                            {{ ucfirst($p->role) }}
                        </div>
                    </div>
                @empty
                    <div class="opacity-70 text-sm">No one joined yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

