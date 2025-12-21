<?php

namespace App\Filament\Resources\Hashtags\Tables;

use App\Models\Hashtag;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class HashtagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tag')
                    ->label('Tag')
                    ->prefix('#')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->sortable(),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Hashtag $record): string => route('hashtags.show', ['tag' => $record->tag]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('posts_count', 'desc');
    }
}
