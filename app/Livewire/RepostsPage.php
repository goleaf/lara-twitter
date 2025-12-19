<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RepostsPage extends Component
{
    use WithPagination;

    public Post $post;

    #[Url]
    public string $tab = 'retweets';

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['user', 'repostOf.user']);

        $primary = $this->primaryPost();

        if (Auth::check() && Auth::user()->isBlockedEitherWay($primary->user)) {
            abort(403);
        }
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function primaryPost(): Post
    {
        if ($this->post->repostOf && $this->post->body === '') {
            return $this->post->repostOf;
        }

        return $this->post;
    }

    private function normalizedTab(): string
    {
        return in_array($this->tab, ['retweets', 'quotes'], true) ? $this->tab : 'retweets';
    }

    public function getRetweetersProperty()
    {
        $primary = $this->primaryPost();

        return Post::query()
            ->where('repost_of_id', $primary->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->with('user')
            ->latest()
            ->paginate(20);
    }

    public function getQuotesProperty()
    {
        $primary = $this->primaryPost();

        return Post::query()
            ->where('repost_of_id', $primary->id)
            ->whereNull('reply_to_id')
            ->where('body', '!=', '')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts', 'replies'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.reposts-page')->layout('layouts.app');
    }
}

