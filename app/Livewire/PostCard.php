<?php

namespace App\Livewire;

use App\Http\Requests\Posts\QuoteRepostRequest;
use App\Models\Post;
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class PostCard extends Component
{
    public Post $post;

    public bool $isQuoting = false;

    public string $quote_body = '';

    public function primaryPost(): Post
    {
        if ($this->post->repostOf && $this->post->body === '') {
            return $this->post->repostOf;
        }

        return $this->post;
    }

    public function toggleLike(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();

        $existing = $post->likes()->where('user_id', Auth::id())->exists();

        if ($existing) {
            $post->likes()->where('user_id', Auth::id())->delete();
        } else {
            $post->likes()->create(['user_id' => Auth::id()]);
        }

        $post->loadCount('likes', 'reposts');

        if ($this->post->relationLoaded('repostOf') && $this->post->repostOf) {
            $this->post->repostOf = $post;
        } else {
            $this->post = $post;
        }
    }

    public function toggleBookmark(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();

        $existing = Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->exists();

        if ($existing) {
            Bookmark::query()
                ->where('user_id', Auth::id())
                ->where('post_id', $post->id)
                ->delete();
        } else {
            Bookmark::query()->create([
                'user_id' => Auth::id(),
                'post_id' => $post->id,
            ]);
        }
    }

    public function hasBookmarked(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $post = $this->primaryPost();

        return Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->exists();
    }

    public function toggleRepost(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();

        $existing = Post::query()
            ->where('user_id', Auth::id())
            ->where('repost_of_id', $post->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Post::query()->create([
                'user_id' => Auth::id(),
                'repost_of_id' => $post->id,
                'body' => '',
            ]);
        }

        $post->loadCount('reposts');

        if ($this->post->relationLoaded('repostOf') && $this->post->repostOf) {
            $this->post->repostOf = $post;
        }
    }

    public function openQuote(): void
    {
        abort_unless(Auth::check(), 403);

        $this->isQuoting = true;
    }

    public function quoteRepost(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();

        $validated = $this->validate(QuoteRepostRequest::rulesFor(Auth::user()));

        Post::query()->create([
            'user_id' => Auth::id(),
            'repost_of_id' => $post->id,
            'body' => $validated['quote_body'],
        ]);

        $post->loadCount('reposts');

        if ($this->post->relationLoaded('repostOf') && $this->post->repostOf) {
            $this->post->repostOf = $post;
        }

        $this->reset('quote_body', 'isQuoting');
        $this->dispatch('post-created');
    }

    public function cancelQuote(): void
    {
        $this->reset('quote_body', 'isQuoting');
    }

    public function quoteBodyHtml(): HtmlString
    {
        return app(\App\Services\PostBodyRenderer::class)->render($this->post->body);
    }

    public function bodyHtml(): HtmlString
    {
        return app(\App\Services\PostBodyRenderer::class)->render($this->primaryPost()->body);
    }

    public function imageUrls(): array
    {
        return $this->primaryPost()
            ->images
            ->map(fn ($image) => Storage::disk('public')->url($image->path))
            ->all();
    }

    public function isRepost(): bool
    {
        return (bool) ($this->post->repost_of_id && $this->post->body === '');
    }

    public function hasRetweeted(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $post = $this->primaryPost();

        return Post::query()
            ->where('user_id', Auth::id())
            ->where('repost_of_id', $post->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->exists();
    }

    public function repostedByLabel(): ?string
    {
        if (! $this->post->repost_of_id) {
            return null;
        }

        if ($this->post->body === '') {
            return $this->post->user->username;
        }

        return null;
    }

    public function deletePost(): void
    {
        abort_unless(Auth::check(), 403);

        if (! $this->canDelete()) {
            abort(403);
        }

        DB::transaction(function (): void {
            // Refresh so we don't delete based on stale state.
            $post = Post::query()->findOrFail($this->post->id);
            $post->delete();
        });

        $this->dispatch('post-created');

        if (request()->routeIs('posts.show')) {
            $this->redirect(route('timeline'), navigate: true);
        }
    }

    public function canDelete(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::id() === $this->post->user_id || (bool) Auth::user()->is_admin;
    }

    public function render()
    {
        return view('livewire.post-card');
    }
}
