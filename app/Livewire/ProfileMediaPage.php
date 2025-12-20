<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithUserProfile;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileMediaPage extends Component
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

        return Post::query()
            ->where('user_id', $this->user->id)
            ->where(function ($q) {
                $q->whereHas('images')->orWhereNotNull('video_path');
            })
            ->withPostCardRelations($viewer, true)
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-media-page')->layout('layouts.app');
    }
}
