<?php

namespace App\Filament\Resources\Mutes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('muter.username')
                    ->label('Muter')
                    ->searchable(),
                TextColumn::make('muted.username')
                    ->label('Muted')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('muter_id')
                    ->label('Muter')
                    ->relationship('muter', 'username'),
                SelectFilter::make('muted_id')
                    ->label('Muted')
                    ->relationship('muted', 'username'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
