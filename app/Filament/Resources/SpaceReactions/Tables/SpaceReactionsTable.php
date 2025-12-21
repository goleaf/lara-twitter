<?php

namespace App\Filament\Resources\SpaceReactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpaceReactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('space_id')
                    ->label('Space')
                    ->sortable(),
                TextColumn::make('space.title')
                    ->label('Title')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('emoji')
                    ->badge()
                    ->label('Emoji'),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('space_id')
                    ->label('Space')
                    ->relationship('space', 'id'),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username'),
            ])
            ->recordActions([
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
