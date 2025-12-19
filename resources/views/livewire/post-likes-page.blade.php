@php($primary = $this->primaryPost())
@php($likers = $this->likers)

<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="text-xl font-semibold">Likes</div>
                        <span class="badge badge-outline badge-sm">{{ $likers->total() }}</span>
                    </div>
                    <div class="text-sm opacity-70">People who liked this post.</div>
                </div>

                <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="space-y-3">
                @forelse ($likers as $like)
                    @php($user = $like->user)
                    @if (! $user)
                        @continue
                    @endif

                    <a class="flex items-center justify-between gap-3 rounded-box border border-base-200 bg-base-100 px-3 py-2 hover:bg-base-200/50 hover:border-base-300 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" href="{{ route('profile.show', ['user' => $user->username]) }}" wire:navigate>
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="avatar">
                                <div class="w-9 rounded-full border border-base-200 bg-base-100">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                    @else
                                        <div class="bg-base-200 grid place-items-center h-full w-full text-xs font-semibold">
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold truncate">
                                    {{ $user->name }}
                                    @if ($user->is_verified)
                                        <x-verified-icon class="ms-1 align-middle" />
                                    @endif
                                </div>
                                <div class="text-xs opacity-60 truncate">&#64;{{ $user->username }}</div>
                            </div>
                        </div>

                        <div class="text-sm opacity-60 shrink-0">{{ $like->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div class="rounded-box border border-base-200 bg-base-200/40 px-4 py-3">
                        <div class="text-sm opacity-70">No likes yet.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="pt-2">
        {{ $likers->links() }}
    </div>
</div>
