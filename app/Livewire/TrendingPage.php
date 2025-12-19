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

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['hashtags', 'keywords'], true) ? $this->tab : 'hashtags';
    }

    public function getTrendingHashtagsProperty()
    {
        return app(TrendingService::class)->trendingHashtags(Auth::user(), 15);
    }

    public function getTrendingKeywordsProperty()
    {
        return app(TrendingService::class)->trendingKeywords(20);
    }

    public function render()
    {
        return view('livewire.trending-page')->layout('layouts.app');
    }
}

