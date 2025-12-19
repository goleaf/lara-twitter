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

    public function mount(string $tag): void
    {
        $this->tag = mb_strtolower($tag);
    }

    public function getHashtagProperty(): ?Hashtag
    {
        return Hashtag::query()->where('tag', $this->tag)->first();
    }

    public function getPostsProperty()
    {
        return Post::query()
            ->whereHas('hashtags', fn ($q) => $q->where('tag', $this->tag))
            ->whereNull('reply_to_id')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.hashtag-page')->layout('layouts.app');
    }
}
