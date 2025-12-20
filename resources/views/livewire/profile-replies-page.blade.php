<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    @include('livewire.partials.profile-header', ['user' => $user, 'active' => 'replies'])

    <div class="space-y-3">
        @forelse ($this->posts as $post)
            <div class="space-y-3" wire:key="profile-reply-{{ $post->id }}">
                @if ($post->reply_to_id && $post->replyTo)
                    <x-replying-to :username="$post->replyTo->user->username" />
                @elseif ($post->is_reply_like)
                    <div class="opacity-70 text-sm">Reply-like post</div>
                @endif
                <livewire:post-card :post="$post" :key="$post->id" />
            </div>
        @empty
            <x-empty-state>
                No replies yet.
            </x-empty-state>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->posts->links() }}
    </div>
</div>
