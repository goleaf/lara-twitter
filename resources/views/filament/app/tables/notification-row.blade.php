@php
    /** @var \Illuminate\Notifications\DatabaseNotification $record */
    $data = $record->data ?? [];
    $type = $data['type'] ?? null;
    $actorUsername = \App\Filament\Pages\App\NotificationsPage::actorUsername($record);
@endphp

<div class="py-3">
    <div class="text-sm font-medium">
        @if ($type === 'post_liked')
            &#64;{{ $actorUsername }} liked your post
        @elseif ($type === 'post_reposted')
            &#64;{{ $actorUsername }}
            {{ ($data['kind'] ?? 'retweet') === 'quote' ? 'quoted your post' : 'retweeted your post' }}
        @elseif ($type === 'post_replied')
            &#64;{{ $actorUsername }} replied to your post
        @elseif ($type === 'post_mentioned')
            &#64;{{ $actorUsername }} mentioned you
        @elseif ($type === 'user_followed')
            &#64;{{ $actorUsername }} followed you
        @elseif ($type === 'message_received')
            &#64;{{ $actorUsername }} sent you a message
        @elseif ($type === 'added_to_list')
            You were added to the list “{{ $data['list_name'] ?? 'a list' }}”
        @elseif ($type === 'followed_user_posted')
            &#64;{{ $actorUsername }} posted
        @else
            Notification
        @endif
    </div>

    @if (! empty($data['excerpt']))
        <div class="text-sm text-gray-600 truncate">
            {{ $data['excerpt'] }}
        </div>
    @endif
</div>

