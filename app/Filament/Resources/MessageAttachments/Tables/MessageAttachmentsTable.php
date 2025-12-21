<?php

namespace App\Filament\Resources\MessageAttachments\Tables;

use App\Filament\Resources\Messages\MessageResource;
use App\Models\MessageAttachment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                Action::make('view-message')
                    ->label('Message')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (MessageAttachment $record): string => MessageResource::getUrl('edit', ['record' => $record->message_id]))
                    ->openUrlInNewTab(),
                Action::make('open-file')
                    ->label('File')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (MessageAttachment $record): string => Storage::disk(config('filesystems.media_disk', 'public'))->url($record->path))
                    ->visible(fn (MessageAttachment $record): bool => filled($record->path))
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
