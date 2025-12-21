<?php

namespace App\Livewire;

use App\Services\TrendingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TrendingPage extends Component
{
    #[Url]
    public string $tab = 'hashtags';

    #[Url]
    public string $loc = '';

    public bool $locIsExplicit = false;

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['hashtags', 'keywords', 'topics', 'conversations'], true) ? $this->tab : 'hashtags';
        $this->locIsExplicit = request()->has('loc');
        $this->loc = $this->normalizedLocation();
    }

    public function updatedLoc(): void
    {
        $this->locIsExplicit = true;
    }

    private function normalizedLocation(): string
    {
        $value = trim($this->loc);

        if ($value === '' && ! $this->locIsExplicit && Auth::check()) {
            $value = trim((string) (Auth::user()->location ?? ''));
        }

        return mb_substr($value, 0, 60);
    }

    public function getTrendingHashtagsProperty()
    {
        return app(TrendingService::class)->trendingHashtags(Auth::user(), 15, $this->normalizedLocation() ?: null);
    }

    public function getTrendingKeywordsProperty()
    {
        return app(TrendingService::class)->trendingKeywords(Auth::user(), 20, $this->normalizedLocation() ?: null);
    }

    public function getTrendingConversationsProperty()
    {
        return app(TrendingService::class)->trendingConversations(Auth::user(), 10, $this->normalizedLocation() ?: null);
    }

    public function getTrendingTopicsProperty()
    {
        return app(TrendingService::class)->trendingTopics(Auth::user(), 10, $this->normalizedLocation() ?: null);
    }

    public function render()
    {
        return view('livewire.trending-page');
    }
}
