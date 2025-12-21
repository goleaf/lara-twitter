<?php

namespace App\Filament\Resources\Blocks\Tables;

use Filament\Actions\DeleteAction;
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
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
