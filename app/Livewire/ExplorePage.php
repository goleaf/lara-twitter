<?php

namespace App\Livewire;

use App\Models\Moment;
use App\Models\User;
use App\Services\DiscoverService;
use App\Services\FollowService;
use App\Services\TrendingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class ExplorePage extends Component
{
    #[Url]
    public string $tab = 'trending';

    public string $q = '';

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['for-you', 'trending', 'news', 'sports', 'entertainment', 'technology'], true) ? $this->tab : 'trending';
    }

    public function search(): void
    {
        $q = trim($this->q);

        if ($q === '') {
            $this->redirectRoute('search', navigate: true);

            return;
        }

        $this->redirectRoute('search', ['q' => $q], navigate: true);
    }

    public function toggleFollow(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::id() === $userId, 403);

        $target = User::query()->findOrFail($userId);
        abort_if(Auth::user()->isBlockedEitherWay($target), 403);

        app(FollowService::class)->toggle(Auth::user(), $target);

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
        return app(TrendingService::class)->trendingKeywords(Auth::user(), 10);
    }

    public function getRecommendedUsersProperty()
    {
        return app(DiscoverService::class)->recommendedUsers(Auth::user(), 8);
    }

    public function getForYouPostsProperty()
    {
        return app(DiscoverService::class)->forYouPosts(Auth::user(), 20);
    }

    public function getTrendingTopicPostsProperty()
    {
        if ($this->tab !== 'trending') {
            return collect();
        }

        $tags = $this->trendingHashtags->take(3)->pluck('tag')->all();

        return app(DiscoverService::class)
            ->topPostsForHashtags($tags, Auth::user(), 2, 3)
            ->filter(fn ($posts) => $posts->isNotEmpty());
    }

    public function getTopStoriesProperty()
    {
        return Moment::query()
            ->where('is_public', true)
            ->with(['owner'])
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();
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
