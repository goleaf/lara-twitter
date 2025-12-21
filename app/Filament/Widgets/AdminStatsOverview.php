<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\SpaceSpeakerRequest;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return Cache::remember('admin:stats-overview', now()->addSeconds(90), function (): array {
            $users = User::query()->count();
            $newUsers = User::query()->where('created_at', '>=', now()->subWeek())->count();

            $postsQuery = Post::query()->withoutGlobalScope('published');
            $posts = (clone $postsQuery)->count();
            $newPosts = (clone $postsQuery)->where('created_at', '>=', now()->subWeek())->count();

            $openReports = Report::query()->where('status', Report::STATUS_OPEN)->count();
            $reviewingReports = Report::query()->where('status', Report::STATUS_REVIEWING)->count();

            $liveSpaces = Space::query()
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->count();

            $pendingSpeakerRequests = SpaceSpeakerRequest::query()
                ->where('status', SpaceSpeakerRequest::STATUS_PENDING)
                ->count();

            $messagesToday = Message::query()->where('created_at', '>=', now()->subDay())->count();

            return [
                Stat::make('Users', number_format($users))
                    ->description(number_format($newUsers) . ' new this week')
                    ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                    ->icon(Heroicon::OutlinedUsers)
                    ->color('success'),
                Stat::make('Posts', number_format($posts))
                    ->description(number_format($newPosts) . ' created this week')
                    ->descriptionIcon(Heroicon::OutlinedChatBubbleLeft)
                    ->icon(Heroicon::OutlinedChatBubbleLeft)
                    ->color('primary'),
                Stat::make('Open Reports', number_format($openReports))
                    ->description(number_format($reviewingReports) . ' in review')
                    ->descriptionIcon(Heroicon::OutlinedFlag)
                    ->icon(Heroicon::OutlinedFlag)
                    ->color($openReports > 0 ? 'warning' : 'success'),
                Stat::make('Live Spaces', number_format($liveSpaces))
                    ->description('Active right now')
                    ->descriptionIcon(Heroicon::OutlinedMicrophone)
                    ->icon(Heroicon::OutlinedMicrophone)
                    ->color('info'),
                Stat::make('Speaker Requests', number_format($pendingSpeakerRequests))
                    ->description('Pending approvals')
                    ->descriptionIcon(Heroicon::OutlinedHandRaised)
                    ->icon(Heroicon::OutlinedHandRaised)
                    ->color($pendingSpeakerRequests > 0 ? 'warning' : 'success'),
                Stat::make('Messages (24h)', number_format($messagesToday))
                    ->description('Direct messages in the last day')
                    ->descriptionIcon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->color('gray'),
            ];
        });
    }
}
