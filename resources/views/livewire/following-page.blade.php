<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xl font-semibold">Following</div>
                    <div class="opacity-70 text-sm">&#64;{{ $user->username }}</div>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('profile.show', ['user' => $user]) }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="space-y-3">
                @forelse ($this->following as $followed)
                    <div class="flex items-center justify-between gap-3">
                        <a class="link link-hover min-w-0" href="{{ route('profile.show', ['user' => $followed->username]) }}" wire:navigate>
                            <span class="font-semibold">{{ $followed->name }}</span>
                            <span class="opacity-60 font-normal">&#64;{{ $followed->username }}</span>
                        </a>

                        <div class="flex items-center gap-2 shrink-0">
                            @auth
                                @if (auth()->id() !== $followed->id)
                                    <button class="btn btn-outline btn-sm" wire:click="toggleFollow({{ $followed->id }})">
                                        {{ $this->isFollowing($followed->id) ? 'Unfollow' : 'Follow' }}
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="opacity-70">Not following anyone yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->following->links() }}
    </div>
</div>

