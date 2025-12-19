@php($primary = $this->primaryPost())

<div class="max-w-2xl mx-auto space-y-4">
    <div>
        <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>← Back to post</a>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="font-semibold">Likes</div>
            <div class="text-sm opacity-70">People who liked this post.</div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="space-y-3">
                @forelse ($this->likers as $like)
                    @php($user = $like->user)
                    @if (! $user)
                        @continue
                    @endif

                    <a class="flex items-center justify-between gap-3 rounded-box px-3 py-2 hover:bg-base-200/70 transition" href="{{ route('profile.show', ['user' => $user->username]) }}" wire:navigate>
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="avatar">
                                <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">{{ $user->name }}</div>
                                <div class="text-xs opacity-60 truncate">&#64;{{ $user->username }}</div>
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">{{ $like->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div class="opacity-70">No likes yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $this->likers->links() }}
    </div>
</div>
