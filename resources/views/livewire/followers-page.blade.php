<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xl font-semibold">Followers</div>
                    <div class="opacity-70 text-sm">&#64;{{ $user->username }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="space-y-3">
                @forelse ($this->followers as $follower)
                    <div class="flex items-center justify-between gap-3">
                        <a class="link link-hover min-w-0" href="{{ route('profile.show', ['user' => $follower->username]) }}" wire:navigate>
                            <span class="font-semibold">{{ $follower->name }}</span>
                            <span class="opacity-60 font-normal">&#64;{{ $follower->username }}</span>
                        </a>

                        <div class="flex items-center gap-2 shrink-0">
                            @auth
                                @if (auth()->id() === $user->id && $follower->id !== $user->id)
                                    <button class="btn btn-ghost btn-sm" wire:click="removeFollower({{ $follower->id }})">
                                        Remove
                                    </button>
                                @endif

                                @if (auth()->id() !== $follower->id)
                                    <button class="btn btn-outline btn-sm" wire:click="toggleFollow({{ $follower->id }})">
                                        {{ $this->isFollowing($follower->id) ? 'Unfollow' : 'Follow' }}
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="opacity-70">No followers yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->followers->links() }}
    </div>
</div>

