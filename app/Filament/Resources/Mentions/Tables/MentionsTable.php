<?php

namespace App\Filament\Resources\Mentions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MentionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('post.body')
                    ->label('Post body')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mentionedUser.username')
                    ->label('Mentioned')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
                SelectFilter::make('mentioned_user_id')
                    ->label('Mentioned user')
                    ->relationship('mentionedUser', 'username'),
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
