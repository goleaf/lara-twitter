<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentReportsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()
                    ->with(['reporter'])
                    ->whereIn('status', [Report::STATUS_OPEN, Report::STATUS_REVIEWING])
                    ->latest()
            )
            ->columns([
                TextColumn::make('case_number')
                    ->label('Case')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reason')
                    ->formatStateUsing(fn (?string $state): string => $state ? Report::reasonLabel($state) : '')
                    ->sortable(),
                TextColumn::make('reporter.username')
                    ->label('Reporter')
                    ->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Filed'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Report $record): string => ReportResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_OPEN)
                    ->action(fn (Report $record): bool => $record->update(['status' => Report::STATUS_REVIEWING])),
                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_RESOLVED)
                    ->action(fn (Report $record): bool => $record->update(['status' => Report::STATUS_RESOLVED])),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_DISMISSED)
                    ->action(fn (Report $record): bool => $record->update(['status' => Report::STATUS_DISMISSED])),
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Report $record): string => ReportResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 20])
            ->defaultPaginationPageOption(5);
    }
}
