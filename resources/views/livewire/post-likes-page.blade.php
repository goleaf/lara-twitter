@php($primary = $this->primaryPost())

<div class="max-w-2xl mx-auto space-y-4">
    <div>
        <a class="link link-hover opacity-70" href="{{ route('posts.show', $primary) }}" wire:navigate>← Back to post</a>
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
                    <a class="flex items-center justify-between link link-hover" href="{{ route('profile.show', ['user' => $like->user->username]) }}" wire:navigate>
                        <span>
                            <span class="font-semibold">{{ $like->user->name }}</span>
                            <span class="opacity-60 font-normal">&#64;{{ $like->user->username }}</span>
                        </span>
                        <span class="text-sm opacity-60">{{ $like->created_at->diffForHumans() }}</span>
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

