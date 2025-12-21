<?php

namespace App\Filament\Resources\Likes\Tables;

use App\Models\Like;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LikesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('post.body')
                    ->label('Post body')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username'),
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
            ])
            ->recordActions([
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Like $record): void {
                        Like::query()
                            ->where('user_id', $record->user_id)
                            ->where('post_id', $record->post_id)
                            ->delete();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
