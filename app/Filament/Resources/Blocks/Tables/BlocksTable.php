<?php

namespace App\Filament\Resources\Blocks\Tables;

use App\Models\Block;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
            ->defaultSort('created_at', 'desc');
    }
}
