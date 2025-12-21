<?php

namespace App\Filament\Resources\Follows\Tables;

use App\Models\Follow;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FollowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('follower.username')
                    ->label('Follower')
                    ->searchable(),
                TextColumn::make('followed.username')
                    ->label('Followed')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('follower_id')
                    ->label('Follower')
                    ->relationship('follower', 'username'),
                SelectFilter::make('followed_id')
                    ->label('Followed')
                    ->relationship('followed', 'username'),
            ])
            ->recordActions([
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Follow $record): void {
                        Follow::query()
                            ->where('follower_id', $record->follower_id)
                            ->where('followed_id', $record->followed_id)
                            ->delete();

                        $record->follower?->flushCachedRelations();
                        $record->followed?->flushCachedRelations();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
