<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('case_number')
                    ->label('Case')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reason')
                    ->formatStateUsing(fn (?string $state): string => $state ? Report::reasonLabel($state) : '')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reporter.username')
                    ->label('Reporter')
                    ->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '')
                    ->sortable(),
                TextColumn::make('reportable_id')
                    ->label('Target ID')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Filed')
                    ->sortable(),
                TextColumn::make('resolvedBy.username')
                    ->label('Resolved By')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resolved_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(Report::statuses(), Report::statuses())),
                SelectFilter::make('reason')
                    ->options(Report::reasonLabels()),
                SelectFilter::make('reportable_type')
                    ->label('Target type')
                    ->options([
                        'App\\Models\\Post' => 'Post',
                        'App\\Models\\Hashtag' => 'Hashtag',
                        'App\\Models\\Space' => 'Space',
                        'App\\Models\\Message' => 'Message',
                        'App\\Models\\User' => 'User',
                        'App\\Models\\UserList' => 'List',
                    ]),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_OPEN)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_REVIEWING])),
                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_RESOLVED)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_RESOLVED])),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_DISMISSED)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_DISMISSED])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
