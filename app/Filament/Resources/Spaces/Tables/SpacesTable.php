<?php

namespace App\Filament\Resources\Spaces\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Space;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SpacesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('description')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('host.username')
                    ->label('Host')
                    ->searchable(),
                IconColumn::make('recording_enabled')
                    ->boolean()
                    ->label('Recording'),
                TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('participants_count')
                    ->counts('participants')
                    ->label('Participants')
                    ->sortable(),
                TextColumn::make('reports_count')
                    ->counts('reports')
                    ->label('Reports')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('live')
                    ->label('Live now')
                    ->query(fn ($query) => $query->whereNotNull('started_at')->whereNull('ended_at')),
                Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(fn ($query) => $query->whereNotNull('scheduled_for')->whereNull('started_at')),
                Filter::make('ended')
                    ->label('Ended')
                    ->query(fn ($query) => $query->whereNotNull('ended_at')),
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
                TernaryFilter::make('recording_enabled')
                    ->label('Recording'),
            ])
            ->recordActions([
                Action::make('view-host')
                    ->label('Host')
                    ->icon('heroicon-o-user')
                    ->url(fn (Space $record): string => UserResource::getUrl('edit', ['record' => $record->host_user_id]))
                    ->openUrlInNewTab(),
                Action::make('start-space')
                    ->label('Start now')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Space $record): bool => ! $record->started_at && ! $record->ended_at)
                    ->action(fn (Space $record): bool => $record->update(['started_at' => now()])),
                Action::make('end-space')
                    ->label('End space')
                    ->icon('heroicon-o-stop-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Space $record): bool => (bool) $record->started_at && ! $record->ended_at)
                    ->action(fn (Space $record): bool => $record->update(['ended_at' => now()])),
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Space $record): string => route('spaces.show', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('start-now')
                        ->label('Start now')
                        ->icon('heroicon-o-play-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $timestamp = now();

                            Space::query()
                                ->whereKey($records->modelKeys())
                                ->whereNull('started_at')
                                ->whereNull('ended_at')
                                ->update(['started_at' => $timestamp]);
                        }),
                    BulkAction::make('end-now')
                        ->label('End now')
                        ->icon('heroicon-o-stop-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $timestamp = now();

                            Space::query()
                                ->whereKey($records->modelKeys())
                                ->whereNotNull('started_at')
                                ->whereNull('ended_at')
                                ->update(['ended_at' => $timestamp]);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
