<?php

namespace App\Filament\Resources\PostPollOptions\Tables;

use App\Filament\Resources\PostPolls\PostPollResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\PostPollOption;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostPollOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_poll_id')
                    ->label('Poll')
                    ->sortable(),
                TextColumn::make('option_text')
                    ->label('Option')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('votes_count')
                    ->label('Votes')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_poll_id')
                    ->label('Poll')
                    ->relationship('poll', 'id'),
                Filter::make('with_votes')
                    ->label('Has votes')
                    ->query(fn ($query) => $query->has('votes')),
            ])
            ->recordActions([
                Action::make('view-poll')
                    ->label('Poll')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (PostPollOption $record): string => PostPollResource::getUrl('edit', ['record' => $record->post_poll_id]))
                    ->openUrlInNewTab(),
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PostPollOption $record): string => PostResource::getUrl('edit', ['record' => $record->poll->post_id]))
                    ->openUrlInNewTab(),
                Action::make('clear-votes')
                    ->label('Clear votes')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (PostPollOption $record): bool => $record->votes_count > 0)
                    ->action(fn (PostPollOption $record): int => $record->votes()->delete()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
