<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @php($previous = url()->previous())
    @php($canNavigateBack = str_starts_with($previous, url('/')))

    <div class="card bg-base-100 border">
        <div class="card-body py-3">
            <div class="flex items-center gap-3">
                <a class="btn btn-ghost btn-sm" href="{{ $previous }}" @if ($canNavigateBack) wire:navigate @endif>
                    Back
                </a>
                <div class="text-xl font-semibold">Post</div>
            </div>
        </div>
    </div>

    @if (count($ancestors))
        <div class="space-y-3">
            @foreach ($ancestors as $ancestor)
                <livewire:post-card :post="$ancestor" :key="'ancestor-'.$ancestor->id" />
            @endforeach
        </div>
    @endif

    <div class="space-y-3">
        @if ($post->reply_to_id && $post->replyTo)
            <x-replying-to :username="$post->replyTo->user->username" />
        @endif

        <livewire:post-card :post="$post" :key="'post-'.$post->id" />
    </div>

    @auth
        @if ($post->canBeRepliedBy(auth()->user()))
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="font-semibold">Reply</div>
                    <livewire:reply-composer :post="$post" :key="'reply-composer-'.$post->id" />
                </div>
            </div>
        @else
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">Replies are limited by the author.</div>
                </div>
            </div>
        @endif
    @endauth

    <div class="pl-4 sm:pl-6 border-l border-base-300 space-y-3">
        @foreach ($this->replies as $reply)
            <livewire:post-card :post="$reply" :key="'reply-'.$reply->id" />
        @endforeach
    </div>

    <div class="pt-2">
        {{ $this->replies->links() }}
    </div>
</div>
