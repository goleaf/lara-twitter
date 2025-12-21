<?php

namespace App\Filament\Resources\PostLinkPreviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                Filter::make('fetched')
                    ->label('Fetched')
                    ->query(fn ($query) => $query->whereNotNull('fetched_at')),
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
