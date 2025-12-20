<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="text-xl font-semibold">Bookmarks</div>
                        <span class="badge badge-outline badge-sm">Private</span>
                        <span class="badge badge-ghost badge-sm">{{ $this->bookmarks->total() }} saved</span>
                    </div>
                    <div class="text-sm opacity-70">Only you can see your saved posts.</div>
                </div>
                <div class="flex items-center gap-3">
                    @if ($this->bookmarks->total())
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="clearAll" wire:loading.attr="disabled" wire:target="clearAll">
                            Clear all
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($this->bookmarks as $bookmark)
            @if ($bookmark->post)
                @php($post = $bookmark->post)
                @if ($post->reply_to_id && $post->replyTo)
                    <div class="opacity-70 text-sm">
                        Replying to
                        <a class="link link-primary" href="{{ route('profile.show', ['user' => $post->replyTo->user->username]) }}" wire:navigate>
                            &#64;{{ $post->replyTo->user->username }}
                        </a>
                    </div>
                @endif
                <livewire:post-card :post="$post" :key="'bookmark-post-'.$bookmark->post_id" />
            @else
                <div class="card bg-base-100 border">
                    <div class="card-body gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold">This post is no longer available</div>
                                <div class="text-sm opacity-70">Bookmarked {{ $bookmark->created_at?->diffForHumans() }}</div>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" wire:click="remove({{ (int) $bookmark->post_id }})" wire:loading.attr="disabled" wire:target="remove({{ (int) $bookmark->post_id }})">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <x-empty-state>
                <x-slot:icon>
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 2a2 2 0 0 0-2 2v18l8-4 8 4V4a2 2 0 0 0-2-2H6Z" />
                    </svg>
                </x-slot:icon>
                No bookmarks yet.
            </x-empty-state>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->bookmarks->links() }}
    </div>
</div>
