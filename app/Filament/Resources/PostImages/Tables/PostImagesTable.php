<?php

namespace App\Filament\Resources\PostImages\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\PostImage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PostImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Image')
                    ->disk('public'),
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
                Filter::make('missing_thumbnail')
                    ->label('Missing thumbnail')
                    ->query(fn ($query) => $query->whereNull('thumbnail_path')),
            ])
            ->recordActions([
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PostImage $record): string => PostResource::getUrl('edit', ['record' => $record->post_id]))
                    ->openUrlInNewTab(),
                Action::make('open-image')
                    ->label('Image')
                    ->icon('heroicon-o-photo')
                    ->url(fn (PostImage $record): string => Storage::disk(config('filesystems.media_disk', 'public'))->url($record->path))
                    ->visible(fn (PostImage $record): bool => filled($record->path))
                    ->openUrlInNewTab(),
                Action::make('open-thumbnail')
                    ->label('Thumbnail')
                    ->icon('heroicon-o-photo')
                    ->url(fn (PostImage $record): string => Storage::disk(config('filesystems.media_disk', 'public'))->url($record->thumbnail_path))
                    ->visible(fn (PostImage $record): bool => filled($record->thumbnail_path))
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
