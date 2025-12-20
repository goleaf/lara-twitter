<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AdminReportsStatusChart extends ChartWidget
{
    protected ?string $heading = 'Reports status';

    protected ?string $description = 'Open vs resolved cases';

    protected string $color = 'warning';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        return Cache::remember('admin:reports-status-chart', now()->addSeconds(90), function (): array {
            $statuses = Report::statuses();

            $counts = Report::query()
                ->selectRaw('status, count(*) as count')
                ->whereIn('status', $statuses)
                ->groupBy('status')
                ->pluck('count', 'status')
                ->mapWithKeys(fn ($count, $status) => [(string) $status => (int) $count])
                ->all();

            $labels = array_map(fn (string $status) => ucfirst($status), $statuses);
            $data = array_map(fn (string $status) => $counts[$status] ?? 0, $statuses);

            return [
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => [
                            'rgba(251, 146, 60, 0.85)',
                            'rgba(250, 204, 21, 0.85)',
                            'rgba(34, 197, 94, 0.85)',
                            'rgba(148, 163, 184, 0.85)',
                        ],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }
}
