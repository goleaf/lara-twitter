@php($primary = $this->primaryPost())
@php($replyingTo = $this->replyingToUsername())

<div class="card bg-base-100 border">
    <div class="card-body gap-2">
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

        <div class="flex items-center justify-between">
            <a class="font-semibold link link-hover" href="{{ route('profile.show', ['user' => $primary->user->username]) }}" wire:navigate>
                {{ $primary->user->name }}
                <span class="opacity-60 font-normal">&#64;{{ $primary->user->username }}</span>
            </a>
            <div class="flex items-center gap-2">
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
                            <a class="font-semibold link link-hover" href="{{ route('profile.show', ['user' => $post->repostOf->user->username]) }}" wire:navigate>
                                {{ $post->repostOf->user->name }}
                                <span class="opacity-60 font-normal">&#64;{{ $post->repostOf->user->username }}</span>
                            </a>
                            <a class="text-sm opacity-60 link link-hover" href="{{ route('posts.show', $post->repostOf) }}" wire:navigate>
                                {{ $post->repostOf->created_at->diffForHumans() }}
                            </a>
                        </div>

                        <div class="prose max-w-none">
                            {!! app(\App\Services\PostBodyRenderer::class)->render($post->repostOf->body) !!}
                        </div>

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

        <div class="flex items-center gap-2 pt-2">
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

            <button wire:click="toggleBookmark" class="btn btn-ghost btn-sm" @disabled(!auth()->check())>
                {{ $this->hasBookmarked() ? 'Bookmarked' : 'Bookmark' }}
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

            <a class="btn btn-ghost btn-sm" href="{{ route('posts.show', $primary) }}" wire:navigate>Reply</a>
        </div>

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
