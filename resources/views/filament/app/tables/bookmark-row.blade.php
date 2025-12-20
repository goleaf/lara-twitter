@php
    /** @var \App\Models\Bookmark $record */
    $record = $getRecord();
    $bookmark = $record;
    $post = $bookmark->post;
    $repostedBy = null;
    $displayPost = $post;

    if ($post && $post->body === '' && $post->repostOf) {
        $repostedBy = $post->user;
        $displayPost = $post->repostOf;
    }

    $user = $displayPost?->user;
    $replyToUser = null;
    if ($displayPost && $displayPost->relationLoaded('replyTo') && $displayPost->replyTo) {
        $replyToUser = $displayPost->replyTo->user;
    }
@endphp

<div class="py-4">
    <div class="rounded-2xl border border-base-200 bg-base-100 px-4 py-3 shadow-sm">
        @if ($displayPost && $user)
            <div class="flex gap-3">
                <div class="shrink-0">
                    @if ($user->avatar_url)
                        <img
                            src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}"
                            class="h-10 w-10 rounded-full object-cover"
                            loading="lazy"
                        />
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-base-200 text-sm font-semibold text-base-content/70">
                            {{ mb_substr($user->name ?? $user->username ?? '?', 0, 1) }}
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-base-content/70">
                        <a href="{{ route('profile.show', ['user' => $user->username]) }}" class="font-semibold text-base-content hover:underline">
                            {{ $user->name }}
                        </a>

                        <span class="text-base-content/60">
                            @{{ $user->username }}
                        </span>

                        <span class="text-base-content/40">·</span>

                        <a href="{{ route('posts.show', $displayPost) }}" class="text-base-content/60 hover:underline">
                            {{ $displayPost->created_at?->diffForHumans() }}
                        </a>

                        <span class="text-base-content/40">·</span>

                        <span class="inline-flex items-center gap-1 rounded-full bg-base-200 px-2 py-0.5 text-xs text-base-content/70">
                            <x-filament::icon icon="heroicon-o-bookmark" class="h-3 w-3" />
                            Bookmarked {{ $bookmark->created_at?->diffForHumans() }}
                        </span>
                    </div>

                    @if ($repostedBy)
                        <div class="mt-1 text-xs text-base-content/60">
                            Reposted by &#64;{{ $repostedBy->username }}
                        </div>
                    @endif

                    @if ($replyToUser)
                        <div class="mt-1 text-sm text-base-content/60">
                            Replying to &#64;{{ $replyToUser->username }}
                        </div>
                    @endif

                    <div class="mt-1 text-sm leading-relaxed">
                        {!! app(\App\Services\PostBodyRenderer::class)->render($displayPost->body, $displayPost->id) !!}
                    </div>

                    @if ($displayPost->relationLoaded('images') && $displayPost->images->isNotEmpty())
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($displayPost->images as $image)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                                    alt="Post image"
                                    class="h-32 w-full rounded-xl object-cover"
                                    loading="lazy"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div>
                <div class="font-semibold">This post is no longer available</div>
                <div class="mt-1 text-sm text-base-content/60">
                    Bookmarked {{ $bookmark->created_at?->diffForHumans() }}
                </div>
            </div>
        @endif
    </div>
</div>
