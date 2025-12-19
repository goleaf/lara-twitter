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

        $target = $this->post->repostOf && $this->post->body === '' ? $this->post->repostOf : $this->post;
        app(AnalyticsService::class)->recordUnique('post_view', $target->id);
    }

    public function getRepliesProperty()
    {
        return Post::query()
            ->where('reply_to_id', $this->post->id)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->oldest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.post-page')->layout('layouts.app');
    }
}
