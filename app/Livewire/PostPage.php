<?php

namespace App\Livewire;

use App\Models\Post;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PostPage extends Component
{
    use WithPagination;

    public Post $post;
    /** @var array<int, \App\Models\Post> */
    public array $ancestors = [];

    protected $listeners = [
        'reply-created' => '$refresh',
    ];

    public function mount(Post $post): void
    {
        $this->post = $post
            ->load([
                'user',
                'images',
                'replyTo.user',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->loadCount(['likes', 'reposts']);

        if (Auth::check()) {
            $viewer = Auth::user();

            $authors = collect([$this->post->user])
                ->merge($this->post->repostOf ? [$this->post->repostOf->user] : [])
                ->filter()
                ->unique('id');

            foreach ($authors as $author) {
                if ($viewer->isBlockedEitherWay($author)) {
                    abort(403);
                }
            }
        }

        $this->ancestors = $this->loadAncestors($this->post);

        $target = $this->post->repostOf && $this->post->body === '' ? $this->post->repostOf : $this->post;
        $target->loadMissing('user');

        if (! ($target->user->analytics_enabled || $target->user->is_admin)) {
            return;
        }

        if (Auth::check() && Auth::id() === $target->user_id) {
            return;
        }

        app(AnalyticsService::class)->recordUnique('post_view', $target->id);

        if ($target->images->isNotEmpty() || $target->video_path) {
            app(AnalyticsService::class)->recordUnique('post_media_view', $target->id);
        }
    }

    /**
     * @return array<int, Post>
     */
    private function loadAncestors(Post $post): array
    {
        $ancestors = [];

        $current = $post;
        $maxDepth = 20;
        $depth = 0;

        while ($current->reply_to_id && $depth < $maxDepth) {
            $parent = Post::query()
                ->with([
                    'user',
                    'images',
                    'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
                ])
                ->withCount(['likes', 'reposts'])
                ->find($current->reply_to_id);

            if (! $parent) {
                break;
            }

            if (Auth::check() && Auth::user()->isBlockedEitherWay($parent->user)) {
                abort(403);
            }

            $ancestors[] = $parent;
            $current = $parent;
            $depth++;
        }

        return array_reverse($ancestors);
    }

    public function getRepliesProperty()
    {
        $query = Post::query()
            ->where('reply_to_id', $this->post->id)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->oldest();

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.post-page')->layout('layouts.app');
    }
}
