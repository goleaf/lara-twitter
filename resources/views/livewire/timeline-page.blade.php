<div class="max-w-2xl mx-auto space-y-4">
    <div class="tabs tabs-boxed">
        <a
            class="tab {{ $feed === 'following' ? 'tab-active' : '' }}"
            href="{{ route('timeline', ['feed' => 'following']) }}"
            wire:navigate
        >
            Following
        </a>
        <a
            class="tab {{ $feed === 'for-you' ? 'tab-active' : '' }}"
            href="{{ route('timeline', ['feed' => 'for-you']) }}"
            wire:navigate
        >
            For You
        </a>
    </div>

    @auth
        <livewire:post-composer />
    @endauth

    <div wire:poll.30s="checkForNewPosts">
        @if ($hasNewPosts)
            <button class="btn btn-primary btn-sm w-full" wire:click="refreshTimeline">
                New posts available — refresh
            </button>
        @endif
    </div>

    <div class="space-y-3">
        @foreach ($this->posts as $post)
            @if ($post->reply_to_id && $post->replyTo)
                <div class="opacity-70 text-sm">
                    Replying to
                    <a class="link link-primary" href="{{ route('profile.show', ['user' => $post->replyTo->user->username]) }}" wire:navigate>
                        &#64;{{ $post->replyTo->user->username }}
                    </a>
                </div>
            @endif
            <livewire:post-card :post="$post" :key="$post->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
