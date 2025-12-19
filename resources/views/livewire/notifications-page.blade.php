<div class="max-w-2xl mx-auto space-y-4">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <div class="flex items-center justify-between gap-3">
                <div class="text-xl font-semibold">Notifications</div>
                <button wire:click="markAllRead" class="btn btn-ghost btn-sm">Mark all as read</button>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($this->notifications as $notification)
            @php($data = $notification->data ?? [])
            @php($type = $data['type'] ?? null)
            @php($isUnread = is_null($notification->read_at))

            @php($postId = $data['post_id'] ?? $data['original_post_id'] ?? null)

            <a
                class="card bg-base-100 border hover:border-base-300 transition {{ $isUnread ? 'border-primary/40' : '' }}"
                href="{{ $postId ? route('posts.show', $postId) : '#' }}"
                @if ($postId) wire:navigate @endif
            >
                <div class="card-body py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold">
                                @if ($type === 'post_liked')
                                    &#64;{{ $data['liked_by_username'] ?? 'someone' }} liked your post
                                @elseif ($type === 'post_reposted')
                                    &#64;{{ $data['reposter_username'] ?? 'someone' }}
                                    {{ ($data['kind'] ?? 'retweet') === 'quote' ? 'quoted your post' : 'retweeted your post' }}
                                @elseif ($type === 'post_replied')
                                    &#64;{{ $data['replier_username'] ?? 'someone' }} replied to your post
                                @elseif ($type === 'post_mentioned')
                                    &#64;{{ $data['mentioned_by_username'] ?? 'someone' }} mentioned you
                                @else
                                    Notification
                                @endif
                            </div>

                            @if (!empty($data['excerpt']))
                                <div class="text-sm opacity-70 truncate">{{ $data['excerpt'] }}</div>
                            @endif
                        </div>

                        <div class="text-sm opacity-60 shrink-0">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="card bg-base-100 border">
                <div class="card-body">
                    <div class="opacity-70">No notifications yet.</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->notifications->links() }}
    </div>
</div>
