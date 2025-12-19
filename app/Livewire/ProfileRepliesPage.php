<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithUserProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileRepliesPage extends Component
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
        return Post::query()
            ->where('user_id', $this->user->id)
            ->where(function ($q) {
                $q->whereNotNull('reply_to_id')->orWhere('is_reply_like', true);
            })
            ->with([
                'user',
                'images',
                'replyTo.user',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts']),
            ])
            ->withCount(['likes', 'reposts'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-replies-page')->layout('layouts.app');
    }
}
