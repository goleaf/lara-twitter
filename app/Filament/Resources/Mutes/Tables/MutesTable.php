<?php

namespace App\Filament\Resources\Mutes\Tables;

use App\Models\Mute;
use Filament\Tables\Actions\Action;
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
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Mute $record): void {
                        Mute::query()
                            ->where('muter_id', $record->muter_id)
                            ->where('muted_id', $record->muted_id)
                            ->delete();

                        $record->muter?->flushCachedRelations();
                        $record->muted?->flushCachedRelations();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
