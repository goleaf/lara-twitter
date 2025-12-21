<?php

namespace App\Filament\Resources\Mutes\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Mute;
use Filament\Actions\Action;
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
                Action::make('view-muter')
                    ->label('Muter')
                    ->icon('heroicon-o-user')
                    ->url(fn (Mute $record): string => UserResource::getUrl('edit', ['record' => $record->muter_id]))
                    ->openUrlInNewTab(),
                Action::make('view-muted')
                    ->label('Muted')
                    ->icon('heroicon-o-user')
                    ->url(fn (Mute $record): string => UserResource::getUrl('edit', ['record' => $record->muted_id]))
                    ->openUrlInNewTab(),
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
            ->defaultKeySort(false)
            ->defaultSort('created_at', 'desc');
    }
}
