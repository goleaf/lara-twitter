<?php

namespace App\Livewire;

use App\Services\TrendingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class TrendingPage extends Component
{
    #[Url]
    public string $tab = 'hashtags';

    #[Url]
    public string $loc = '';

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['hashtags', 'keywords'], true) ? $this->tab : 'hashtags';
        $this->loc = $this->normalizedLocation();
    }

    private function normalizedLocation(): string
    {
        $value = trim($this->loc);

        if ($value === '' && Auth::check()) {
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
        return app(TrendingService::class)->trendingKeywords(20, $this->normalizedLocation() ?: null);
    }

    public function render()
    {
        return view('livewire.trending-page')->layout('layouts.app');
    }
}
