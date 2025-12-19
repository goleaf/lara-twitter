<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\DiscoverService;
use App\Services\TrendingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class ExplorePage extends Component
{
    #[Url]
    public string $tab = 'trending';

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['for-you', 'trending', 'news', 'sports', 'entertainment', 'technology'], true) ? $this->tab : 'trending';
    }

    public function toggleFollow(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $userId, 403);

        $viewer = Auth::user();

        $isFollowing = $viewer->following()->where('followed_id', $userId)->exists();

        if ($isFollowing) {
            $viewer->following()->detach($userId);
        } else {
            $viewer->following()->attach($userId);
        }

        $this->dispatch('$refresh');
    }

    public function isFollowing(int $userId): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->following()->where('followed_id', $userId)->exists();
    }

    public function getTrendingHashtagsProperty()
    {
        return app(TrendingService::class)->trendingHashtags(Auth::user(), 10);
    }

    public function getTrendingKeywordsProperty()
    {
        return app(TrendingService::class)->trendingKeywords(10);
    }

    public function getRecommendedUsersProperty()
    {
        return app(DiscoverService::class)->recommendedUsers(Auth::user(), 8);
    }

    public function getForYouPostsProperty()
    {
        return app(DiscoverService::class)->forYouPosts(Auth::user(), 20);
    }

    public function getCategoryPostsProperty()
    {
        if (in_array($this->tab, ['trending', 'for-you'], true)) {
            return collect();
        }

        return app(DiscoverService::class)->categoryPosts($this->tab, Auth::user(), 20);
    }

    public function render()
    {
        return view('livewire.explore-page')->layout('layouts.app');
    }
}
