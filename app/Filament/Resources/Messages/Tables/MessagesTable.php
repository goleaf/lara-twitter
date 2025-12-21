<?php

namespace App\Filament\Resources\Messages\Tables;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MessagesTable
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
                    ->label('Sender')
                    ->searchable(),
                TextColumn::make('body')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('attachments_count')
                    ->label('Files')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reactions_count')
                    ->label('Reactions')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Sent')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->since()
                    ->label('Deleted')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
                Filter::make('with_attachments')
                    ->label('Has attachments')
                    ->query(fn ($query) => $query->whereHas('attachments')),
            ])
            ->recordActions([
                Action::make('view-conversation')
                    ->label('Conversation')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn ($record): string => ConversationResource::getUrl('edit', ['record' => $record->conversation_id]))
                    ->openUrlInNewTab(),
                Action::make('view-sender')
                    ->label('Sender')
                    ->icon('heroicon-o-user')
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
