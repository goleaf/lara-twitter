<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileLikesPage extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount(['followers', 'following']);
    }

    public function getPostsProperty()
    {
        return $this->user
            ->likedPosts()
            ->getQuery()
            ->whereNull('reply_to_id')
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest('likes.created_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-likes-page')->layout('layouts.app');
    }
}

