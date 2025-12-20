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
        $viewer = Auth::user();

        $this->post = Post::query()
            ->withPostCardRelations($viewer)
            ->with([
                'replyTo:id,user_id',
                'replyTo.user:id,username',
            ])
            ->findOrFail($post->id);

        if ($viewer) {
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
        $viewer = Auth::user();

        $current = $post;
        $maxDepth = 20;
        $depth = 0;

        while ($current->reply_to_id && $depth < $maxDepth) {
            $parent = Post::query()
                ->withPostCardRelations($viewer)
                ->find($current->reply_to_id);

            if (! $parent) {
                break;
            }

            if ($viewer && $viewer->isBlockedEitherWay($parent->user)) {
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
        $viewer = Auth::user();

        $query = Post::query()
            ->where('reply_to_id', $this->post->id)
            ->withPostCardRelations($viewer)
            ->oldest()
            ->orderBy('id');

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
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
