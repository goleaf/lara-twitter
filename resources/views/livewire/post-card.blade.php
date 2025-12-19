@php($primary = $this->primaryPost())
@php($replyingTo = $this->replyingToUsername())

<div class="card bg-base-100 card-hover">
    <div class="card-body p-4">
        <div class="flex gap-3">
            <a class="shrink-0" href="{{ route('profile.show', ['user' => $primary->user->username, 'from_post' => $primary->id]) }}" wire:navigate aria-label="View profile">
                <div class="avatar">
                    <div class="w-10 rounded-full border border-base-200 bg-base-100">
                        @if ($primary->user->avatar_url)
                            <img src="{{ $primary->user->avatar_url }}" alt="" />
                        @else
                            <div class="bg-base-200 grid place-items-center h-full w-full text-sm font-semibold">
                                {{ mb_strtoupper(mb_substr($primary->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </a>

            <div class="min-w-0 flex-1 flex flex-col gap-2">
        @if ($this->isRepost())
            <div class="flex items-center gap-2 text-xs opacity-70">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 1l4 4-4 4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11V9a4 4 0 0 1 4-4h14" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 23l-4-4 4-4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13v2a4 4 0 0 1-4 4H3" />
                </svg>
                <span>
                    Retweeted by
                    <a class="link link-hover" href="{{ route('profile.show', ['user' => $post->user->username]) }}" wire:navigate>
                        &#64;{{ $post->user->username }}
                    </a>
                </span>
            </div>
        @endif

        @if ($replyingTo)
            <div class="flex items-center gap-2 text-xs opacity-70">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 0 1-4-.78L3 20l1.3-3.9A7.6 7.6 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                </svg>
                <span>
                    Replying to
                    <a class="link link-hover" href="{{ route('profile.show', ['user' => $replyingTo]) }}" wire:navigate>
                        &#64;{{ $replyingTo }}
                    </a>
                </span>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <a class="font-semibold link link-hover truncate" href="{{ route('profile.show', ['user' => $primary->user->username, 'from_post' => $primary->id]) }}" wire:navigate>
                {{ $primary->user->name }}
                @if ($primary->user->is_verified)
                    <x-verified-icon class="ms-1 align-middle" />
                @endif
                <span class="opacity-60 font-normal">&#64;{{ $primary->user->username }}</span>
            </a>
            <div class="flex items-center gap-2 shrink-0">
                <a class="text-sm opacity-60 link link-hover" href="{{ route('posts.show', $primary) }}" wire:navigate>
                    {{ $primary->created_at->diffForHumans() }}
                </a>
                @if ($primary->location)
                    <span class="text-sm opacity-60">· {{ $primary->location }}</span>
                @endif

                @auth
                    @if ($this->canDelete() || auth()->id() !== $primary->user_id)
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square" aria-label="More actions">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 border">
                                @if ($this->canDelete())
                                    <li>
                                        <button type="button" wire:click="deletePost" class="text-error">
                                            Delete
                                        </button>
                                    </li>
                                @endif

                                @if (auth()->id() !== $primary->user_id)
                                    <li>
                                        <livewire:report-button
                                            :reportable-type="\App\Models\Post::class"
                                            :reportable-id="$primary->id"
                                            label="Report"
                                            button-class="btn btn-ghost btn-sm justify-start w-full"
                                            :show-notice="false"
                                            :key="'report-post-'.$primary->id"
                                        />
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <div class="prose max-w-none">
            {!! $this->bodyHtml() !!}
        </div>

        @if ($primary->poll)
            @php($poll = $primary->poll)
            @php($options = $poll->options)
            @php($totalVotes = (int) $options->sum('votes_count'))
            @php($myVoteOptionId = $this->pollVoteOptionId($poll->id))
            @php($showResults = $poll->ends_at->isPast() || $myVoteOptionId)

            <div class="pt-2">
                <div class="card bg-base-200 shadow-none">
                    <div class="card-body gap-2">
                        @if ($showResults)
                            @foreach ($options as $option)
                                @php($count = (int) ($option->votes_count ?? 0))
                                @php($pct = $totalVotes ? (int) round(($count / $totalVotes) * 100) : 0)

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="{{ (int) $myVoteOptionId === (int) $option->id ? 'font-semibold' : '' }}">
                                            {{ $option->option_text }}
                                        </span>
                                        <span class="opacity-70">{{ $pct }}%</span>
                                    </div>
                                    <progress class="progress progress-primary w-full" value="{{ $pct }}" max="100"></progress>
                                </div>
                            @endforeach

                            <div class="text-xs opacity-70">
                                {{ $totalVotes }} vote{{ $totalVotes === 1 ? '' : 's' }}
                                ·
                                {{ $poll->ends_at->isPast() ? 'Final results' : 'Poll ends '.$poll->ends_at->diffForHumans() }}
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach ($options as $option)
                                    <button
                                        type="button"
                                        class="btn btn-outline btn-sm w-full justify-start"
                                        wire:click="voteInPoll({{ $option->id }})"
                                    >
                                        {{ $option->option_text }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="text-xs opacity-70">Poll ends {{ $poll->ends_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($primary->linkPreview)
            @php($preview = $primary->linkPreview)
            @php($host = parse_url($preview->url, PHP_URL_HOST))

            <div class="pt-2">
                <a
                    class="block"
                    href="{{ route('links.redirect', ['post' => $primary->id, 'u' => $preview->url]) }}"
                    target="_blank"
                    rel="nofollow noopener noreferrer"
                >
                    <div class="card bg-base-200 shadow-none overflow-hidden">
                        @if ($preview->image_url)
                            <figure>
                                <img class="w-full max-h-48 object-cover" src="{{ $preview->image_url }}" alt="" loading="lazy" />
                            </figure>
                        @endif

                        <div class="card-body p-3 gap-1">
                            <div class="text-xs opacity-70">{{ $preview->site_name ?? $host ?? $preview->url }}</div>
                            <div class="font-semibold leading-snug">{{ $preview->title ?? $host ?? $preview->url }}</div>
                            @if ($preview->description)
                                <div class="text-sm opacity-70">{{ $preview->description }}</div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if ($primary->video_path)
            <div class="pt-2">
                <video class="w-full rounded-box border" controls preload="metadata">
                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primary->video_path) }}" type="{{ $primary->video_mime_type ?? 'video/mp4' }}" />
                </video>
            </div>
        @endif

	        @php($urls = $this->imageUrls())
	        @php($imageCount = count($urls))
	        @if ($imageCount)
	            @php($gridClass = $imageCount === 1 ? 'grid-cols-1' : 'grid-cols-2')
	            <div class="grid {{ $gridClass }} gap-2 pt-2">
	                @foreach ($urls as $url)
	                    @php($isWide = $imageCount === 1 || ($imageCount === 3 && $loop->last))
	                    @php($spanClass = $imageCount === 3 && $loop->last ? 'col-span-2' : '')
	                    @php($ratio = $isWide ? '16 / 9' : '1 / 1')
	
	                    <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200 {{ $spanClass }}" style="aspect-ratio: {{ $ratio }};">
	                        <img class="h-full w-full object-cover" src="{{ $url }}" alt="" loading="lazy" />
	                    </div>
	                @endforeach
	            </div>
	        @endif

        @if ($post->repostOf && ! $this->isRepost())
            <div class="pt-2">
                <div class="card bg-base-200 shadow-none">
                    <div class="card-body gap-2">
                        <div class="flex items-center justify-between">
                            <a class="font-semibold link link-hover" href="{{ route('profile.show', ['user' => $post->repostOf->user->username, 'from_post' => $post->repostOf->id]) }}" wire:navigate>
                                {{ $post->repostOf->user->name }}
                                <span class="opacity-60 font-normal">&#64;{{ $post->repostOf->user->username }}</span>
                            </a>
                            <div class="flex items-center gap-2 shrink-0">
                                <a class="text-sm opacity-60 link link-hover" href="{{ route('posts.show', $post->repostOf) }}" wire:navigate>
                                    {{ $post->repostOf->created_at->diffForHumans() }}
                                </a>
                                @if ($post->repostOf->location)
                                    <span class="text-sm opacity-60">· {{ $post->repostOf->location }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="prose max-w-none">
                            {!! app(\App\Services\PostBodyRenderer::class)->render($post->repostOf->body, $post->repostOf->id) !!}
                        </div>

                        @if ($post->repostOf->poll)
                            @php($poll = $post->repostOf->poll)
                            @php($options = $poll->options)
                            @php($totalVotes = (int) $options->sum('votes_count'))
                            @php($myVoteOptionId = $this->pollVoteOptionId($poll->id))
                            @php($showResults = $poll->ends_at->isPast() || $myVoteOptionId)

                            <div class="pt-2">
                                <div class="card bg-base-100 shadow-none">
                                    <div class="card-body gap-2">
                                        @if ($showResults)
                                            @foreach ($options as $option)
                                                @php($count = (int) ($option->votes_count ?? 0))
                                                @php($pct = $totalVotes ? (int) round(($count / $totalVotes) * 100) : 0)

                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span class="{{ (int) $myVoteOptionId === (int) $option->id ? 'font-semibold' : '' }}">
                                                            {{ $option->option_text }}
                                                        </span>
                                                        <span class="opacity-70">{{ $pct }}%</span>
                                                    </div>
                                                    <progress class="progress progress-primary w-full" value="{{ $pct }}" max="100"></progress>
                                                </div>
                                            @endforeach

                                            <div class="text-xs opacity-70">
                                                {{ $totalVotes }} vote{{ $totalVotes === 1 ? '' : 's' }}
                                                ·
                                                {{ $poll->ends_at->isPast() ? 'Final results' : 'Poll ends '.$poll->ends_at->diffForHumans() }}
                                            </div>
                                        @else
                                            <div class="space-y-2">
                                                @foreach ($options as $option)
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline btn-sm w-full justify-start"
                                                        wire:click="voteInPoll({{ $option->id }})"
                                                    >
                                                        {{ $option->option_text }}
                                                    </button>
                                                @endforeach
                                            </div>

                                            <div class="text-xs opacity-70">Poll ends {{ $poll->ends_at->diffForHumans() }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($post->repostOf->linkPreview)
                            @php($preview = $post->repostOf->linkPreview)
                            @php($host = parse_url($preview->url, PHP_URL_HOST))

                            <div class="pt-2">
                                <a
                                    class="block"
                                    href="{{ route('links.redirect', ['post' => $post->repostOf->id, 'u' => $preview->url]) }}"
                                    target="_blank"
                                    rel="nofollow noopener noreferrer"
                                >
                                    <div class="card bg-base-100 shadow-none overflow-hidden">
                                        @if ($preview->image_url)
                                            <figure>
                                                <img class="w-full max-h-48 object-cover" src="{{ $preview->image_url }}" alt="" loading="lazy" />
                                            </figure>
                                        @endif

                                        <div class="card-body p-3 gap-1">
                                            <div class="text-xs opacity-70">{{ $preview->site_name ?? $host ?? $preview->url }}</div>
                                            <div class="font-semibold leading-snug">{{ $preview->title ?? $host ?? $preview->url }}</div>
                                            @if ($preview->description)
                                                <div class="text-sm opacity-70">{{ $preview->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif

                        @if ($post->repostOf->video_path)
                            <div class="pt-2">
                                <video class="w-full rounded-box border" controls preload="metadata">
                                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->repostOf->video_path) }}" type="{{ $post->repostOf->video_mime_type ?? 'video/mp4' }}" />
                                </video>
                            </div>
                        @endif

	                        @php($repostImagesCount = $post->repostOf->images->count())
	                        @if ($repostImagesCount)
	                            @php($gridClass = $repostImagesCount === 1 ? 'grid-cols-1' : 'grid-cols-2')
	                            <div class="grid {{ $gridClass }} gap-2 pt-2">
	                                @foreach ($post->repostOf->images as $image)
	                                    @php($isWide = $repostImagesCount === 1 || ($repostImagesCount === 3 && $loop->last))
	                                    @php($spanClass = $repostImagesCount === 3 && $loop->last ? 'col-span-2' : '')
	                                    @php($ratio = $isWide ? '16 / 9' : '1 / 1')
	
	                                    <div class="relative overflow-hidden rounded-box border border-base-200 bg-base-200 {{ $spanClass }}" style="aspect-ratio: {{ $ratio }};">
	                                        <img class="h-full w-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" loading="lazy" />
	                                    </div>
	                                @endforeach
	                            </div>
	                        @endif
                    </div>
                </div>
            </div>
        @endif

        @php($likesCount = (int) ($primary->likes_count ?? $primary->likes()->count()))
        @php($repostsCount = (int) ($primary->reposts_count ?? $primary->reposts()->count()))
        @php($repliesCount = is_numeric($primary->replies_count ?? null) ? (int) $primary->replies_count : null)

        @php($likesBadge = $likesCount ? 'badge-neutral' : 'badge-ghost')
        @php($repostsBadge = $repostsCount ? 'badge-neutral' : 'badge-ghost')
        @php($repliesBadge = ($repliesCount ?? 0) ? 'badge-neutral' : 'badge-ghost')

        <div class="flex flex-wrap items-center gap-1 pt-2">
            @auth
                <button
                    wire:click="toggleReplyComposer"
                    class="btn btn-ghost btn-sm btn-square {{ $isReplying ? 'text-primary' : '' }}"
                    aria-label="{{ $isReplying ? 'Cancel reply' : 'Reply' }}"
                    title="{{ $isReplying ? 'Cancel reply' : 'Reply' }}"
                    aria-pressed="{{ $isReplying ? 'true' : 'false' }}"
                >
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 0 1-4-.78L3 20l1.3-3.9A7.6 7.6 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                    </svg>
                </button>
            @else
                <button class="btn btn-ghost btn-sm btn-square" disabled aria-label="Reply">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a10.3 10.3 0 0 1-4-.78L3 20l1.3-3.9A7.6 7.6 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                    </svg>
                </button>
            @endauth

            <a class="btn btn-ghost btn-sm gap-2" href="{{ route('posts.show', $primary) }}" wire:navigate aria-label="Open thread">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 3h-6m6 0v6m0-6L10.5 13.5" />
                </svg>
                <span class="hidden sm:inline">Thread</span>
                @if (! is_null($repliesCount))
                    <span class="badge badge-sm {{ $repliesBadge }} tabular-nums">{{ $repliesCount }}</span>
                @endif
            </a>

            <div class="w-px h-6 bg-base-200 mx-1"></div>

            <button
                wire:click="toggleRepost"
                class="btn btn-ghost btn-sm btn-square {{ $this->hasRetweeted() ? 'text-success' : '' }}"
                @disabled(!auth()->check())
                aria-label="{{ $this->hasRetweeted() ? 'Undo retweet' : 'Retweet' }}"
                title="{{ $this->hasRetweeted() ? 'Undo retweet' : 'Retweet' }}"
                aria-pressed="{{ $this->hasRetweeted() ? 'true' : 'false' }}"
            >
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 1l4 4-4 4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11V9a4 4 0 0 1 4-4h14" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 23l-4-4 4-4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13v2a4 4 0 0 1-4 4H3" />
                </svg>
            </button>

            <a class="btn btn-ghost btn-sm gap-2" href="{{ route('posts.reposts', $primary) }}" wire:navigate aria-label="View reposts">
                <span class="hidden sm:inline">Reposts</span>
                <span class="badge badge-sm {{ $repostsBadge }} tabular-nums">{{ $repostsCount }}</span>
            </a>

            <button
                wire:click="openQuote"
                class="btn btn-ghost btn-sm btn-square {{ $isQuoting ? 'text-primary' : '' }}"
                @disabled(!auth()->check())
                aria-label="Quote"
                title="Quote"
                aria-pressed="{{ $isQuoting ? 'true' : 'false' }}"
            >
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 0 1 3.182 3.182L7.5 19.313l-4.5 1.125 1.125-4.5L16.862 3.487Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6.75 17.25 4.5" />
                </svg>
            </button>

            <button
                wire:click="toggleLike"
                class="btn btn-ghost btn-sm btn-square {{ $this->hasLiked() ? 'text-error' : '' }}"
                @disabled(!auth()->check())
                aria-label="{{ $this->hasLiked() ? 'Unlike' : 'Like' }}"
                title="{{ $this->hasLiked() ? 'Unlike' : 'Like' }}"
                aria-pressed="{{ $this->hasLiked() ? 'true' : 'false' }}"
            >
                @if ($this->hasLiked())
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21s-7-4.35-7-11.5S8.5 2 12 7.5C15.5 2 19 2 19 9.5S12 21 12 21Z" />
                    </svg>
                @else
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.687-4.5-1.935 0-3.597 1.126-4.313 2.733-.716-1.607-2.378-2.733-4.313-2.733C5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                @endif
            </button>

            <a class="btn btn-ghost btn-sm gap-2" href="{{ route('posts.likes', $primary) }}" wire:navigate aria-label="View likes">
                <span class="hidden sm:inline">Likes</span>
                <span class="badge badge-sm {{ $likesBadge }} tabular-nums">{{ $likesCount }}</span>
            </a>

            <button
                wire:click="toggleBookmark"
                class="btn btn-ghost btn-sm btn-square {{ $this->hasBookmarked() ? 'text-primary' : '' }}"
                @disabled(!auth()->check())
                aria-label="Bookmark"
                title="{{ $this->hasBookmarked() ? 'Remove bookmark' : 'Bookmark' }}"
                aria-pressed="{{ $this->hasBookmarked() ? 'true' : 'false' }}"
            >
                @if ($this->hasBookmarked())
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 2a2 2 0 0 0-2 2v18l8-5 8 5V4a2 2 0 0 0-2-2H6Z" />
                    </svg>
                @else
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 21l-5-3-5 3V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16Z" />
                    </svg>
                @endif
            </button>
        </div>

        @if ($replyError)
            <div class="pt-2 text-error text-sm">{{ $replyError }}</div>
        @endif

        @if ($isReplying || $showThread)
            <div class="pt-3 space-y-3 pl-6 border-l border-base-300">
                @if ($isReplying)
                    <div class="card bg-base-200 shadow-none">
                        <div class="card-body gap-3">
                            <div class="font-semibold">Reply</div>
                            <livewire:reply-composer :post="$primary" :key="'inline-reply-composer-'.$post->id" />
                        </div>
                    </div>
                @endif

                @if ($showThread)
                    @php($threadReplies = $this->threadReplies)
                    @if ($threadReplies->isNotEmpty())
                        @foreach ($threadReplies as $reply)
                            <livewire:post-card :post="$reply" :key="'thread-reply-'.$post->id.'-'.$reply->id" />
                        @endforeach
                    @else
                        <div class="text-sm opacity-60">No replies yet.</div>
                    @endif

                    <div class="flex items-center justify-between gap-2">
                        <a class="link link-primary text-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>
                            View full thread
                        </a>
                        <button type="button" wire:click="hideThread" class="btn btn-ghost btn-xs">Hide</button>
                    </div>
                @endif
            </div>
        @endif

        @if ($isQuoting)
            <div class="pt-3">
                <div class="card bg-base-200 shadow-none">
                    <div class="card-body">
                        <div class="font-semibold">Quote tweet</div>

                        <form wire:submit="quoteRepost" class="space-y-3">
                            <div>
                                <textarea
                                    wire:model="quote_body"
                                    class="textarea textarea-bordered textarea-sm w-full"
                                    rows="3"
                                    placeholder="Add your commentary..."
                                ></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('quote_body')" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="cancelQuote" class="btn btn-ghost btn-sm">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
            </div>
        </div>
    </div>
</div>
