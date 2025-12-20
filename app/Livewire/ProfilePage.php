<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithUserProfile;
use App\Models\Post;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfilePage extends Component
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

        if ($this->user->pinned_post_id) {
            $viewer = Auth::user();
            $pinnedPost = Post::query()
                ->withPostCardRelations($viewer)
                ->find($this->user->pinned_post_id);

            if ($pinnedPost) {
                $this->user->setRelation('pinnedPost', $pinnedPost);
            }
        }

        if (! ($this->user->analytics_enabled || $this->user->is_admin)) {
            return;
        }

        if (Auth::check() && Auth::id() === $this->user->id) {
            return;
        }

        app(AnalyticsService::class)->recordUnique('profile_view', $this->user->id);
        $this->recordProfileClickFromPost();
    }

    private function recordProfileClickFromPost(): void
    {
        $fromPost = request()->query('from_post');

        $postId = filter_var($fromPost, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (! $postId) {
            return;
        }

        $belongsToProfile = Post::query()
            ->where('id', $postId)
            ->where('user_id', $this->user->id)
            ->exists();

        if (! $belongsToProfile) {
            return;
        }

        app(AnalyticsService::class)->recordUnique('post_profile_click', $postId);
    }

    public function getPostsProperty()
    {
        $viewer = Auth::user();

        return Post::query()
            ->where('user_id', $this->user->id)
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->when($this->user->pinned_post_id, fn ($q) => $q->where('id', '!=', $this->user->pinned_post_id))
            ->withPostCardRelations($viewer)
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.profile-page')->layout('layouts.app');
    }
}
