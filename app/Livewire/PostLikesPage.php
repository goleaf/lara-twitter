<?php

namespace App\Livewire;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PostLikesPage extends Component
{
    use WithPagination;

    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['user', 'repostOf.user']);

        $primary = $this->primaryPost();

        if (Auth::check() && Auth::user()->isBlockedEitherWay($primary->user)) {
            abort(403);
        }
    }

    public function primaryPost(): Post
    {
        if ($this->post->repostOf && $this->post->body === '') {
            return $this->post->repostOf;
        }

        return $this->post;
    }

    public function getLikersProperty()
    {
        $primary = $this->primaryPost();

        return Like::query()
            ->where('post_id', $primary->id)
            ->with('user')
            ->latest()
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.post-likes-page')->layout('layouts.app');
    }
}

