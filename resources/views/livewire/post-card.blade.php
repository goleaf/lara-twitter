@php($primary = $this->primaryPost())
@php($replyingTo = $this->replyingToUsername())

<div class="card bg-base-100 border">
    <div class="card-body">
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
            <div class="text-sm opacity-70">
                Retweeted by
                <a class="link link-hover" href="{{ route('profile.show', ['user' => $post->user->username]) }}" wire:navigate>
                    &#64;{{ $post->user->username }}
                </a>
            </div>
        @endif

        @if ($replyingTo)
            <div class="text-sm opacity-70">
                Replying to
                <a class="link link-hover" href="{{ route('profile.show', ['user' => $replyingTo]) }}" wire:navigate>
                    &#64;{{ $replyingTo }}
                </a>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <a class="font-semibold link link-hover truncate" href="{{ route('profile.show', ['user' => $primary->user->username, 'from_post' => $primary->id]) }}" wire:navigate>
                {{ $primary->user->name }}
                <span class="opacity-60 font-normal">&#64;{{ $primary->user->username }}</span>
            </a>
            <div class="flex items-center gap-2 shrink-0">
                <a class="text-sm opacity-60 link link-hover" href="{{ route('posts.show', $primary) }}" wire:navigate>
                    {{ $primary->created_at->diffForHumans() }}
                </a>

                @auth
                    @if ($this->canDelete())
                        <button wire:click="deletePost" class="btn btn-ghost btn-xs btn-error">
                            Delete
                        </button>
                    @endif

                    @if (auth()->id() !== $primary->user_id)
                        <livewire:report-button :reportable-type="\App\Models\Post::class" :reportable-id="$primary->id" :key="'report-post-'.$primary->id" />
                    @endif
                @endauth
            </div>
        </div>

        <div class="prose max-w-none">
            {!! $this->bodyHtml() !!}
        </div>

        @if ($primary->video_path)
            <div class="pt-2">
                <video class="w-full rounded-box border" controls preload="metadata">
                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primary->video_path) }}" type="{{ $primary->video_mime_type ?? 'video/mp4' }}" />
                </video>
            </div>
        @endif

        @php($urls = $this->imageUrls())
        @if (count($urls))
            <div class="grid grid-cols-2 gap-2 pt-2">
                @foreach ($urls as $url)
                    <img class="rounded-box border" src="{{ $url }}" alt="" loading="lazy" />
                @endforeach
            </div>
        @endif

        @if ($post->repostOf && ! $this->isRepost())
            <div class="pt-2">
                <div class="card bg-base-200 border">
                    <div class="card-body gap-2">
                        <div class="flex items-center justify-between">
                            <a class="font-semibold link link-hover" href="{{ route('profile.show', ['user' => $post->repostOf->user->username, 'from_post' => $post->repostOf->id]) }}" wire:navigate>
                                {{ $post->repostOf->user->name }}
                                <span class="opacity-60 font-normal">&#64;{{ $post->repostOf->user->username }}</span>
                            </a>
                            <a class="text-sm opacity-60 link link-hover" href="{{ route('posts.show', $post->repostOf) }}" wire:navigate>
                                {{ $post->repostOf->created_at->diffForHumans() }}
                            </a>
                        </div>

                        <div class="prose max-w-none">
                            {!! app(\App\Services\PostBodyRenderer::class)->render($post->repostOf->body, $post->repostOf->id) !!}
                        </div>

                        @if ($post->repostOf->video_path)
                            <div class="pt-2">
                                <video class="w-full rounded-box border" controls preload="metadata">
                                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->repostOf->video_path) }}" type="{{ $post->repostOf->video_mime_type ?? 'video/mp4' }}" />
                                </video>
                            </div>
                        @endif

                        @if ($post->repostOf->images->count())
                            <div class="grid grid-cols-2 gap-2 pt-2">
                                @foreach ($post->repostOf->images as $image)
                                    <img class="rounded-box border" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="" loading="lazy" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2 pt-2">
            <button
                wire:click="toggleLike"
                class="btn btn-ghost btn-sm {{ $this->hasLiked() ? 'text-error' : '' }}"
                @disabled(!auth()->check())
                aria-label="Like"
                title="Like"
            >
                <span class="text-lg leading-none">♥</span>
            </button>

            <a class="btn btn-ghost btn-sm" href="{{ route('posts.likes', $primary) }}" wire:navigate>
                Likes <span class="badge badge-neutral">{{ $primary->likes_count ?? $primary->likes()->count() }}</span>
            </a>

            <button
                wire:click="toggleBookmark"
                class="btn btn-ghost btn-sm {{ $this->hasBookmarked() ? 'text-primary' : '' }}"
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

            <button wire:click="toggleRepost" class="btn btn-ghost btn-sm" @disabled(!auth()->check())>
                {{ $this->hasRetweeted() ? 'Retweeted' : 'Retweet' }}
            </button>

            <a class="btn btn-ghost btn-sm" href="{{ route('posts.reposts', $primary) }}" wire:navigate>
                Reposts <span class="badge badge-neutral">{{ $primary->reposts_count ?? $primary->reposts()->count() }}</span>
            </a>

            <button wire:click="openQuote" class="btn btn-ghost btn-sm" @disabled(!auth()->check())>
                Quote
            </button>

            @auth
                <button wire:click="toggleReplyComposer" class="btn btn-ghost btn-sm">
                    {{ $isReplying ? 'Cancel' : 'Reply' }}
                </button>
            @else
                <button class="btn btn-ghost btn-sm" disabled>Reply</button>
            @endauth

            <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>Thread</a>
        </div>

        @if ($replyError)
            <div class="pt-2 text-error text-sm">{{ $replyError }}</div>
        @endif

        @if ($isReplying || $showThread)
            <div class="pt-3 space-y-3 pl-6 border-l border-base-300">
                @if ($isReplying)
                    <div class="card bg-base-200 border">
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
                <div class="card bg-base-200 border">
                    <div class="card-body">
                        <div class="font-semibold">Quote tweet</div>

                        <form wire:submit="quoteRepost" class="space-y-3">
                            <div>
                                <textarea
                                    wire:model="quote_body"
                                    class="textarea textarea-bordered w-full"
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
