<?php

namespace App\Filament\Resources\PostPollVotes\Tables;

use App\Filament\Resources\PostPolls\PostPollResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\PostPollVote;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
                Action::make('view-poll')
                    ->label('Poll')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (PostPollVote $record): string => PostPollResource::getUrl('edit', ['record' => $record->post_poll_id]))
                    ->openUrlInNewTab(),
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PostPollVote $record): string => PostResource::getUrl('edit', ['record' => $record->poll->post_id]))
                    ->openUrlInNewTab(),
                Action::make('view-user')
                    ->label('User')
                    ->icon('heroicon-o-user')
                    ->url(fn (PostPollVote $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
