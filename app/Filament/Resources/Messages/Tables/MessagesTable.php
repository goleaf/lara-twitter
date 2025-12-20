<?php

namespace App\Filament\Resources\Messages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
