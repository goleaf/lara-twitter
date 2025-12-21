<?php

namespace App\Filament\Resources\Blocks\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Block;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class BlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('blocker.username')
                    ->label('Blocker')
                    ->searchable(),
                TextColumn::make('blocked.username')
                    ->label('Blocked')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('blocker_id')
                    ->label('Blocker')
                    ->relationship('blocker', 'username'),
                SelectFilter::make('blocked_id')
                    ->label('Blocked')
                    ->relationship('blocked', 'username'),
            ])
            ->recordActions([
                Action::make('view-blocker')
                    ->label('Blocker')
                    ->icon('heroicon-o-user')
                    ->url(fn (Block $record): string => UserResource::getUrl('edit', ['record' => $record->blocker_id]))
                    ->openUrlInNewTab(),
                Action::make('view-blocked')
                    ->label('Blocked')
                    ->icon('heroicon-o-user')
                    ->url(fn (Block $record): string => UserResource::getUrl('edit', ['record' => $record->blocked_id]))
                    ->openUrlInNewTab(),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Block $record): void {
                        Block::query()
                            ->where('blocker_id', $record->blocker_id)
                            ->where('blocked_id', $record->blocked_id)
                            ->delete();

                        $record->blocker?->flushCachedRelations();
                        $record->blocked?->flushCachedRelations();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Block $record): void {
                                Block::query()
                                    ->where('blocker_id', $record->blocker_id)
                                    ->where('blocked_id', $record->blocked_id)
                                    ->delete();

                                $record->blocker?->flushCachedRelations();
                                $record->blocked?->flushCachedRelations();
                            });
                        }),
                ]),
            ])
            ->defaultKeySort(false)
            ->defaultSort('created_at', 'desc');
    }
}
