<div class="max-w-2xl lg:max-w-4xl mx-auto space-y-4">
    <div class="card bg-base-100 border hero-card notifications-hero">
        <div class="hero-edge" aria-hidden="true"></div>
        <div class="card-body">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="text-xl font-semibold">Notifications</div>
                        <span class="badge badge-ghost badge-sm">{{ $this->notifications->total() }} total</span>
                    </div>
                    <div class="text-sm opacity-70">Mentions, likes, reposts, and messages in one place.</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-xs uppercase tracking-[0.35em] text-base-content/60">Inbox</div>
                    <button type="button" wire:click="markAllRead" class="btn btn-ghost btn-sm" wire:loading.attr="disabled" wire:target="markAllRead">
                        Mark all as read
                    </button>
                </div>
            </div>

            <div class="tabs tabs-boxed mt-4">
                <button type="button" class="tab {{ $tab === 'all' ? 'tab-active' : '' }}" wire:click="$set('tab', 'all')">
                    All
                </button>
                <button type="button" class="tab {{ $tab === 'verified' ? 'tab-active' : '' }}" wire:click="$set('tab', 'verified')">
                    Verified
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($this->notifications as $notification)
            @php($data = $notification->data ?? [])
            @php($type = $data['type'] ?? null)
            @php($isUnread = is_null($notification->read_at))

            @php($actorUserId = is_numeric($data['actor_user_id'] ?? null) ? (int) $data['actor_user_id'] : null)
            @php($actor = $actorUserId ? ($actorUsers->get($actorUserId)) : null)
            @php($actorUsername = $data['actor_username'] ?? $data['follower_username'] ?? $data['sender_username'] ?? 'someone')
            @php($avatarLabel = $actor?->name ?? $actorUsername)
            @php($avatarInitial = mb_strtoupper(mb_substr($avatarLabel, 0, 1)))

            @php($iconClass = match ($type) {
                'post_liked' => 'bg-error text-error-content',
                'post_reposted' => 'bg-success text-success-content',
                'post_replied' => 'bg-info text-info-content',
                'post_mentioned' => 'bg-info text-info-content',
                'user_followed' => 'bg-primary text-primary-content',
                'message_received' => 'bg-secondary text-secondary-content',
                'added_to_list' => 'bg-accent text-accent-content',
                'followed_user_posted' => 'bg-neutral text-neutral-content',
                default => 'bg-base-200 text-base-content',
            })

            @php($postId = $data['post_id'] ?? $data['original_post_id'] ?? null)
            @php($conversationId = $data['conversation_id'] ?? null)
            @php($profileUsername = $data['follower_username'] ?? $data['actor_username'] ?? null)

            @php($href = '#')

            @if ($type === 'message_received' && $conversationId)
                @php($href = route('messages.show', $conversationId))
            @elseif ($type === 'user_followed' && $profileUsername)
                @php($href = route('profile.show', ['user' => $profileUsername]))
            @elseif ($type === 'added_to_list' && ($data['list_id'] ?? null))
                @php($href = route('lists.show', $data['list_id']))
            @elseif ($postId)
                @php($href = route('posts.show', $postId))
            @endif

            <a
                class="card bg-base-100 card-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 {{ $isUnread ? 'ring-1 ring-primary/20' : '' }}"
                href="{{ $href }}"
                wire:click.prevent="open('{{ $notification->id }}')"
                wire:loading.class="pointer-events-none opacity-70"
                wire:target="open('{{ $notification->id }}')"
                wire:key="notification-{{ $notification->id }}"
            >
                <div class="card-body py-4">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 pt-0.5">
                            <div class="indicator">
                                <span class="indicator-item indicator-bottom indicator-end">
                                    <span class="w-6 h-6 rounded-full border border-base-100 shadow-sm {{ $iconClass }} grid place-items-center">
                                        @switch($type)
                                            @case('post_liked')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 21s-7-4.35-7-11.5S8.5 2 12 7.5C15.5 2 19 2 19 9.5S12 21 12 21Z" />
                                                </svg>
                                                @break
                                            @case('post_reposted')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 1l4 4-4 4" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11V9a4 4 0 014-4h14" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 23l-4-4 4-4" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13v2a4 4 0 01-4 4H3" />
                                                </svg>
                                                @break
                                            @case('post_replied')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9H5l7-7v18l-7-7h5" />
                                                </svg>
                                                @break
                                            @case('post_mentioned')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a4 4 0 11-8 0 4 4 0 018 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-3.314 0-6 2.239-6 5h12c0-2.761-2.686-5-6-5Z" />
                                                </svg>
                                                @break
                                            @case('user_followed')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a6 6 0 00-12 0" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 13a4 4 0 100-8 4 4 0 000 8Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6m3-3h-6" />
                                                </svg>
                                                @break
                                            @case('message_received')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 01-4-.78L3 20l1.3-3.9A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                                                </svg>
                                                @break
                                            @case('added_to_list')
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                                </svg>
                                                @break
                                            @default
                                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z" />
                                                </svg>
                                        @endswitch
                                    </span>
                                </span>

                                <div class="avatar">
                                    <div class="w-10 rounded-full border border-base-200 bg-base-100">
                                        @if ($actor?->avatar_url)
                                            <img src="{{ $actor->avatar_url }}" alt="" loading="lazy" decoding="async" />
                                        @else
                                            <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                                {{ $avatarInitial }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold leading-snug">
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

                                        @if ($actor?->is_verified)
                                            <x-verified-icon class="ms-1 align-middle" />
                                        @endif
                                    </div>

                                    @if (!empty($data['excerpt']))
                                        <div class="text-sm opacity-70 truncate">{{ $data['excerpt'] }}</div>
                                    @endif
                                </div>

                                <div class="text-xs opacity-60 shrink-0">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <x-empty-state>
                No notifications yet.
            </x-empty-state>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $this->notifications->links() }}
    </div>
</div>
