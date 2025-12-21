<?php

namespace App\Filament\Resources\ConversationParticipants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ConversationParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('conversation_id')
                    ->label('Conversation')
                    ->sortable(),
                TextColumn::make('conversation.title')
                    ->label('Title')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                IconColumn::make('is_request')
                    ->boolean()
                    ->label('Request'),
                IconColumn::make('is_pinned')
                    ->boolean()
                    ->label('Pinned'),
                TextColumn::make('role')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_read_at')
                    ->dateTime()
                    ->label('Last read')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Joined')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_request')
                    ->label('Request'),
                TernaryFilter::make('is_pinned')
                    ->label('Pinned'),
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
