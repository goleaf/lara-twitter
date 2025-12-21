<?php

namespace App\Filament\Resources\PostLinkPreviews\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\PostLinkPreview;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostLinkPreviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('site_name')
                    ->label('Site')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fetched_at')
                    ->dateTime()
                    ->label('Fetched')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
                Filter::make('fetched')
                    ->label('Fetched')
                    ->query(fn ($query) => $query->whereNotNull('fetched_at')),
                Filter::make('has_image')
                    ->label('Has image')
                    ->query(fn ($query) => $query->whereNotNull('image_url')),
            ])
            ->recordActions([
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PostLinkPreview $record): string => PostResource::getUrl('edit', ['record' => $record->post_id]))
                    ->openUrlInNewTab(),
                Action::make('open-url')
                    ->label('Open URL')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PostLinkPreview $record): string => $record->url)
                    ->visible(fn (PostLinkPreview $record): bool => filled($record->url))
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
