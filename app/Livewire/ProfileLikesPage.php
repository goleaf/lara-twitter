<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithUserProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileLikesPage extends Component
{
    use WithPagination;
    use InteractsWithUserProfile;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount(['followers', 'following']);

        if (Auth::check() && Auth::id() !== $this->user->id) {
            abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);
        }
    }

    public function getPostsProperty()
    {
        $query = $this->user
            ->likedPosts()
            ->getQuery()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest('likes.created_at');

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('posts.user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-likes-page')->layout('layouts.app');
    }
}
