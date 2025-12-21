<?php

namespace App\Filament\Resources\MessageAttachments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessageAttachmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('message_id')
                    ->label('Message')
                    ->sortable(),
                TextColumn::make('path')
                    ->label('Path')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->label('MIME')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Order')
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
