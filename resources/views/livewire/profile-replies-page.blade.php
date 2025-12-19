<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @include('livewire.partials.profile-header', ['user' => $user, 'active' => 'replies'])

        <div class="space-y-3">
            @forelse ($this->posts as $post)
                @if ($post->reply_to_id && $post->replyTo)
                    <div class="opacity-70 text-sm">
                        Replying to
                        <a class="link link-primary" href="{{ route('profile.show', ['user' => $post->replyTo->user->username]) }}" wire:navigate>
                            &#64;{{ $post->replyTo->user->username }}
                        </a>
                    </div>
                @elseif ($post->is_reply_like)
                    <div class="opacity-70 text-sm">Reply-like post</div>
                @endif
                <livewire:post-card :post="$post" :key="$post->id" />
            @empty
                <div class="card bg-base-100 border">
                    <div class="card-body">
                        <div class="opacity-70">No replies yet.</div>
                    </div>
                </div>
            @endforelse
        </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
