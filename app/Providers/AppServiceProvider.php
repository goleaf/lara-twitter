<?php

namespace App\Providers;

use App\Livewire\Widgets\TrendingTopicsWidget;
use App\Livewire\Widgets\WhoToFollowWidget;
use App\Models\Post;
use App\Models\Like;
use App\Observers\LikeObserver;
use App\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        Like::observe(LikeObserver::class);

        Livewire::component('widgets.trending-topics-widget', TrendingTopicsWidget::class);
        Livewire::component('widgets.who-to-follow-widget', WhoToFollowWidget::class);
    }
}
