<?php

namespace App\Filament\Resources\MessageReactions\Tables;

use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\MessageReaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessageReactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('message_id')
                    ->label('Message')
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('emoji')
                    ->badge()
                    ->label('Emoji')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('message_id')
                    ->label('Message')
                    ->relationship('message', 'id'),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username'),
            ])
            ->recordActions([
                Action::make('view-message')
                    ->label('Message')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (MessageReaction $record): string => MessageResource::getUrl('edit', ['record' => $record->message_id]))
                    ->openUrlInNewTab(),
                Action::make('view-user')
                    ->label('User')
                    ->icon('heroicon-o-user')
                    ->url(fn (MessageReaction $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
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
