<?php

namespace App\Livewire;

use App\Models\Hashtag;
use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class HashtagPage extends Component
{
    use WithPagination;

    #[Url]
    public string $tag = '';

    #[Url]
    public string $sort = 'latest';

    public function mount(string $tag): void
    {
        $this->tag = mb_strtolower($tag);
        $this->sort = $this->normalizedSort();

        $clean = ltrim($this->tag, '#');
        abort_unless((bool) preg_match('/^[A-Za-z0-9_]{1,50}$/', $clean), 404);
        $this->tag = $clean;
    }

    public function updatedSort(): void
    {
        $this->resetPage();
        $this->sort = $this->normalizedSort();
    }

    private function normalizedSort(): string
    {
        return in_array($this->sort, ['latest', 'top'], true) ? $this->sort : 'latest';
    }

    public function getHashtagProperty(): ?Hashtag
    {
        return Hashtag::query()->where('tag', $this->tag)->first();
    }

    public function getPostsProperty()
    {
        $query = Post::query()
            ->whereHas('hashtags', fn ($q) => $q->where('tag', $this->tag))
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts', 'replies']);

        if ($this->normalizedSort() === 'top') {
            return $query
                ->orderByRaw('(likes_count * 2 + reposts_count * 3 + replies_count) desc')
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return $query->latest()->paginate(15);
    }

    public function render()
    {
        return view('livewire.hashtag-page')->layout('layouts.app');
    }
}
