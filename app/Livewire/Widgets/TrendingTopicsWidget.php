<?php

namespace App\Livewire\Widgets;

use App\Services\TrendingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TrendingTopicsWidget extends Component
{
    #[Computed]
    public function trendingHashtags()
    {
        return app(TrendingService::class)
            ->trendingHashtags(Auth::user(), 6, $this->normalizedViewerLocation());
    }

    #[Computed]
    public function trendingKeywords()
    {
        return app(TrendingService::class)
            ->trendingKeywords(Auth::user(), 6, $this->normalizedViewerLocation());
    }

    private function normalizedViewerLocation(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        $value = trim((string) (Auth::user()->location ?? ''));

        return $value === '' ? null : mb_substr($value, 0, 60);
    }

    public function render()
    {
        return view('livewire.widgets.trending-topics-widget');
    }
}
