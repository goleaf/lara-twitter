<?php

namespace App\Filament\Resources\PostPolls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class PostPollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->label('Ends')
                    ->sortable(),
                TextColumn::make('options_count')
                    ->label('Options')
                    ->sortable(),
                TextColumn::make('votes_count')
                    ->label('Votes')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active')
                    ->query(fn ($query) => $query->where(function ($query) {
                        $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
                    })),
                Filter::make('ended')
                    ->label('Ended')
                    ->query(fn ($query) => $query->whereNotNull('ends_at')->where('ends_at', '<=', now())),
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
