<?php

namespace App\Livewire;

use App\Models\Moment;
use App\Models\User;
use App\Services\DiscoverService;
use App\Services\FollowService;
use App\Services\TrendingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;

class ExplorePage extends Component
{
    #[Url]
    public string $tab = 'trending';

    public string $q = '';

    private ?Collection $trendingHashtagsCache = null;
    private ?Collection $trendingKeywordsCache = null;
    private ?Collection $recommendedUsersCache = null;
    private ?array $followingIdsCache = null;

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['for-you', 'trending', 'news', 'politics', 'sports', 'entertainment', 'technology'], true) ? $this->tab : 'trending';
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

        return in_array($userId, $this->followingIds, true);
    }

    public function getTrendingHashtagsProperty()
    {
        return $this->trendingHashtagsCache ??= app(TrendingService::class)
            ->trendingHashtags(Auth::user(), 10);
    }

    public function getTrendingKeywordsProperty()
    {
        return $this->trendingKeywordsCache ??= app(TrendingService::class)
            ->trendingKeywords(Auth::user(), 10);
    }

    public function getRecommendedUsersProperty()
    {
        return $this->recommendedUsersCache ??= app(DiscoverService::class)
            ->recommendedUsers(Auth::user(), 8);
    }

    public function getFollowingIdsProperty(): array
    {
        if ($this->followingIdsCache !== null) {
            return $this->followingIdsCache;
        }

        if (! Auth::check()) {
            $this->followingIdsCache = [];

            return $this->followingIdsCache;
        }

        $recommendedIds = $this->recommendedUsers->pluck('id')->all();

        if (! count($recommendedIds)) {
            $this->followingIdsCache = [];

            return $this->followingIdsCache;
        }

        $this->followingIdsCache = Auth::user()
            ->following()
            ->whereIn('users.id', $recommendedIds)
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $this->followingIdsCache;
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

    public function getTrendingConversationsProperty()
    {
        if ($this->tab !== 'trending') {
            return collect();
        }

        return app(TrendingService::class)->trendingConversations(Auth::user(), 6);
    }

    public function getTopStoriesProperty()
    {
        return Cache::remember('explore:top-stories', now()->addSeconds(90), function () {
            return Moment::query()
                ->select(['id', 'owner_id', 'title', 'description', 'cover_image_path', 'is_public'])
                ->where('is_public', true)
                ->with([
                    'owner:id,username',
                    'firstItem:id,moment_id,post_id,sort_order',
                    'firstItem.post:id,user_id,body,created_at,repost_of_id',
                    'firstItem.post.repostOf:id,user_id,body,created_at',
                ])
                ->withCount('items')
                ->latest()
                ->limit(5)
                ->get();
        });
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
