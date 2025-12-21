<?php

namespace App\Filament\Resources\PostPollVotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostPollVotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_poll_id')
                    ->label('Poll')
                    ->sortable(),
                TextColumn::make('option.option_text')
                    ->label('Option')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Voted')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_poll_id')
                    ->label('Poll')
                    ->relationship('poll', 'id'),
                SelectFilter::make('post_poll_option_id')
                    ->label('Option')
                    ->relationship('option', 'option_text'),
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
