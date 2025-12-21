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
        $viewer = Auth::user();

        return Post::query()
            ->where('user_id', $this->user->id)
            ->where(function ($q) {
                $q->whereNotNull('reply_to_id')->orWhere('is_reply_like', true);
            })
            ->withPostCardRelations($viewer)
            ->with([
                'replyTo:id,user_id',
                'replyTo.user:id,username',
            ])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-replies-page');
    }
}
