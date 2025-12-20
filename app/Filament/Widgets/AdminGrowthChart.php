<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Growth (last 14 days)';

    protected ?string $description = 'New users and posts';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        return Cache::remember('admin:growth-chart', now()->addSeconds(90), function (): array {
            $days = collect(range(13, 0))
                ->map(fn (int $offset) => now()->subDays($offset)->startOfDay());

            $labels = $days->map(fn ($date) => $date->format('M j'))->all();

            $start = $days->first();
            $end = $days->last()->endOfDay();

            $userCounts = $this->dailyCounts(
                User::query(),
                $start,
                $end,
                'created_at'
            );

            $postCounts = $this->dailyCounts(
                Post::query()->withoutGlobalScope('published'),
                $start,
                $end,
                'created_at'
            );

            return [
                'datasets' => [
                    [
                        'label' => 'New users',
                        'data' => $this->mapDailySeries($days, $userCounts),
                        'borderColor' => 'rgba(14, 165, 233, 0.9)',
                        'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                    [
                        'label' => 'Posts',
                        'data' => $this->mapDailySeries($days, $postCounts),
                        'borderColor' => 'rgba(249, 115, 22, 0.9)',
                        'backgroundColor' => 'rgba(249, 115, 22, 0.12)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    private function dailyCounts($query, $start, $end, string $column): array
    {
        return $query
            ->whereBetween($column, [$start, $end])
            ->selectRaw('date(' . $column . ') as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->mapWithKeys(fn ($count, $day) => [(string) $day => (int) $count])
            ->all();
    }

    /**
     * @param Collection<int, \Illuminate\Support\Carbon> $days
     * @param array<string, int> $counts
     * @return array<int, int>
     */
    private function mapDailySeries(Collection $days, array $counts): array
    {
        return $days
            ->map(fn ($date) => $counts[$date->toDateString()] ?? 0)
            ->values()
            ->all();
    }
}
