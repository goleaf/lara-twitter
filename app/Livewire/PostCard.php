<?php

namespace App\Livewire;

use App\Http\Requests\Posts\QuoteRepostRequest;
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\PostPollOption;
use App\Models\PostPollVote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class PostCard extends Component
{
    public Post $post;

    public int $primaryId;

    public bool $bookmarked = false;

    public bool $liked = false;

    public bool $reposted = false;

    public bool $isQuoting = false;

    public bool $isReplying = false;

    public bool $showThread = false;

    public string $quote_body = '';

    public ?string $replyError = null;

    protected $listeners = [
        'reply-created.{primaryId}' => 'handleReplyCreated',
    ];

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing([
            'linkPreview',
            'repostOf.linkPreview',
            'poll.options' => fn ($q) => $q->withCount('votes'),
            'repostOf.poll.options' => fn ($q) => $q->withCount('votes'),
        ]);
        $this->primaryId = ($post->repost_of_id && $post->body === '') ? (int) $post->repost_of_id : (int) $post->id;

        if (Auth::check()) {
            $viewerId = Auth::id();
            $primary = $this->primaryPost();
            $attributes = $primary->getAttributes();

            $this->bookmarked = array_key_exists('bookmarked_by_viewer', $attributes)
                ? (bool) $primary->bookmarked_by_viewer
                : Bookmark::query()
                    ->where('user_id', $viewerId)
                    ->where('post_id', $this->primaryId)
                    ->exists();

            $this->liked = array_key_exists('liked_by_viewer', $attributes)
                ? (bool) $primary->liked_by_viewer
                : $primary->likes()
                    ->where('user_id', $viewerId)
                    ->exists();

            $this->reposted = array_key_exists('reposted_by_viewer', $attributes)
                ? (bool) $primary->reposted_by_viewer
                : Post::query()
                    ->where('user_id', $viewerId)
                    ->where('repost_of_id', $this->primaryId)
                    ->whereNull('reply_to_id')
                    ->where('body', '')
                    ->exists();
        }
    }

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
        $post->loadMissing('user');
        abort_if(Auth::user()->isBlockedEitherWay($post->user), 403);

        $existing = $post->likes()->where('user_id', Auth::id())->exists();

        if ($existing) {
            $post->likes()->where('user_id', Auth::id())->delete();
        } else {
            $post->likes()->create(['user_id' => Auth::id()]);
        }

        $this->liked = ! $existing;

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
        $post->loadMissing('user');
        abort_if(Auth::user()->isBlockedEitherWay($post->user), 403);

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

        $this->bookmarked = ! $existing;
        $this->dispatch('bookmark-toggled');
    }

    public function hasBookmarked(): bool
    {
        return $this->bookmarked;
    }

    public function toggleRepost(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();
        $post->loadMissing('user');
        abort_if(Auth::user()->isBlockedEitherWay($post->user), 403);

        $existing = Post::query()
            ->where('user_id', Auth::id())
            ->where('repost_of_id', $post->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->first();

        if ($existing) {
            $existing->delete();
            $this->reposted = false;
        } else {
            Post::query()->create([
                'user_id' => Auth::id(),
                'repost_of_id' => $post->id,
                'body' => '',
            ]);
            $this->reposted = true;
        }

        $post->loadCount('reposts');

        if ($this->post->relationLoaded('repostOf') && $this->post->repostOf) {
            $this->post->repostOf = $post;
        }

        $this->dispatch('post-created');
    }

    public function openQuote(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();
        $post->loadMissing('user');
        abort_if(Auth::user()->isBlockedEitherWay($post->user), 403);

        $this->isQuoting = true;
    }

    public function quoteRepost(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();
        $post->loadMissing('user');
        abort_if(Auth::user()->isBlockedEitherWay($post->user), 403);

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
        return app(\App\Services\PostBodyRenderer::class)->render($this->post->body, $this->post->id);
    }

    public function replyingToUsername(): ?string
    {
        $post = $this->primaryPost();

        if (! $post->is_reply_like) {
            return null;
        }

        $body = ltrim((string) $post->body);

        if (preg_match('/^@([A-Za-z0-9_-]{1,30})(?![A-Za-z0-9_-])/', $body, $m)) {
            return mb_strtolower($m[1]);
        }

        return null;
    }

    public function toggleReplyComposer(): void
    {
        abort_unless(Auth::check(), 403);

        $post = $this->primaryPost();
        $post->loadMissing('user');

        if (! $post->canBeRepliedBy(Auth::user())) {
            $this->replyError = 'Replies are limited by the author.';
            $this->isReplying = false;

            return;
        }

        $this->replyError = null;
        $this->isQuoting = false;
        $this->isReplying = ! $this->isReplying;
        $this->showThread = $this->showThread || $this->isReplying;
    }

    public function hideThread(): void
    {
        $this->showThread = false;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getThreadRepliesProperty(): Collection
    {
        $limit = 3;
        $viewer = Auth::user();

        $query = Post::query()
            ->withPostCardRelations($viewer)
            ->where('reply_to_id', $this->primaryId)
            ->latest()
            ->limit($limit);

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        return $query->get()->reverse()->values();
    }

    public function handleReplyCreated(): void
    {
        if ($this->isReplying) {
            $this->isReplying = false;
            $this->showThread = true;
        }

        $this->replyError = null;
    }

    public function bodyHtml(): HtmlString
    {
        $primary = $this->primaryPost();

        return app(\App\Services\PostBodyRenderer::class)->render($primary->body, $primary->id);
    }

    public function pollVoteOptionId(int $pollId): ?int
    {
        if (! Auth::check()) {
            return null;
        }

        return PostPollVote::query()
            ->where('post_poll_id', $pollId)
            ->where('user_id', Auth::id())
            ->value('post_poll_option_id');
    }

    public function voteInPoll(int $optionId): void
    {
        abort_unless(Auth::check(), 403);

        $option = PostPollOption::query()
            ->with('poll')
            ->findOrFail($optionId);

        $poll = $option->poll;
        abort_unless((bool) $poll, 404);

        abort_if($poll->ends_at->isPast(), 403);

        $allowedPostIds = [$this->primaryPost()->id];
        if ($this->post->repostOf) {
            $allowedPostIds[] = $this->post->repostOf->id;
        }

        abort_unless(in_array((int) $poll->post_id, array_unique($allowedPostIds), true), 403);

        $alreadyVoted = PostPollVote::query()
            ->where('post_poll_id', $poll->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyVoted) {
            return;
        }

        PostPollVote::query()->create([
            'post_poll_id' => $poll->id,
            'post_poll_option_id' => $option->id,
            'user_id' => Auth::id(),
        ]);

        $this->post->load([
            'poll.options' => fn ($q) => $q->withCount('votes'),
            'repostOf.poll.options' => fn ($q) => $q->withCount('votes'),
        ]);
    }

    public function imageUrls(): array
    {
        $disk = config('filesystems.media_disk', 'public');

        return $this->primaryPost()
            ->images
            ->map(fn ($image) => Storage::disk($disk)->url($image->path))
            ->all();
    }

    public function isRepost(): bool
    {
        return (bool) ($this->post->repost_of_id && $this->post->body === '');
    }

    public function hasRetweeted(): bool
    {
        return Auth::check() && $this->reposted;
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

    public function hasLiked(): bool
    {
        return $this->liked;
    }

    public function render()
    {
        return view('livewire.post-card');
    }
}
