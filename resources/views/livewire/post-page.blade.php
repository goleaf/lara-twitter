<div class="max-w-2xl mx-auto space-y-4">
    <div>
        <a class="link link-hover opacity-70" href="{{ url()->previous() }}">← Back</a>
    </div>

    <div class="space-y-3">
        @if ($post->reply_to_id && $post->replyTo)
            <div class="opacity-70 text-sm">
                Replying to
                <a class="link link-primary" href="{{ route('profile.show', ['user' => $post->replyTo->user->username]) }}" wire:navigate>
                    &#64;{{ $post->replyTo->user->username }}
                </a>
            </div>
        @endif

        <livewire:post-card :post="$post" :key="'post-'.$post->id" />
    </div>

    @auth
        <div class="card bg-base-100 border">
            <div class="card-body">
                <div class="font-semibold">Reply</div>
                <livewire:reply-composer :post="$post" :key="'reply-composer-'.$post->id" />
            </div>
        </div>
    @endauth

    <div class="space-y-3">
        @foreach ($this->replies as $reply)
            <livewire:post-card :post="$reply" :key="'reply-'.$reply->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->replies->links() }}
    </div>
</div>
