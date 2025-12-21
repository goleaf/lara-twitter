<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithUserProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
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
        $viewer = Auth::user();

        $query = $this->user
            ->likedPosts()
            ->getQuery()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->withPostCardRelations($viewer)
            ->latest('likes.created_at')
            ->orderByDesc('posts.id');

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('posts.user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-likes-page');
    }
}
