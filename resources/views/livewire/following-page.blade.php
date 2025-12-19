<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
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
            <div class="space-y-2">
                @forelse ($this->following as $followed)
                    <div class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition focus-within:ring-2 focus-within:ring-primary/20">
                        <a class="flex items-center gap-3 min-w-0 focus:outline-none" href="{{ route('profile.show', ['user' => $followed->username]) }}" wire:navigate>
                            <div class="avatar">
                                <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                    @if ($followed->avatar_url)
                                        <img src="{{ $followed->avatar_url }}" alt="" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($followed->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">
                                    {{ $followed->name }}
                                    @if ($followed->is_verified)
                                        <x-verified-icon class="ms-1 align-middle" />
                                    @endif
                                </div>
                                <div class="text-xs opacity-60 truncate">&#64;{{ $followed->username }}</div>
                            </div>
                        </a>

                        <div class="flex items-center gap-2 shrink-0">
                            @auth
                                @if (auth()->id() !== $followed->id)
                                    <button class="btn btn-sm {{ $this->isFollowing($followed->id) ? 'btn-outline' : 'btn-primary' }}" wire:click="toggleFollow({{ $followed->id }})">
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
