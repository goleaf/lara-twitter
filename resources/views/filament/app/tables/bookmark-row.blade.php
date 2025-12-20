@php
    /** @var \App\Models\Bookmark $record */
    $bookmark = $record;
    $post = $bookmark->post;
@endphp

<div class="py-4">
    @if ($post)
        @if ($post->reply_to_id && $post->replyTo)
            <div class="text-sm text-gray-600">
                Replying to &#64;{{ $post->replyTo->user->username }}
            </div>
        @endif

        <div class="mt-1">
            {!! app(\App\Services\PostBodyRenderer::class)->render($post->body, $post->id) !!}
        </div>
    @else
        <div class="font-semibold">This post is no longer available</div>
        <div class="text-sm text-gray-600">
            Bookmarked {{ $bookmark->created_at?->diffForHumans() }}
        </div>
    @endif
</div>

