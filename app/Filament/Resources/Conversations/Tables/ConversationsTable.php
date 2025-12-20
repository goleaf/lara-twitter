<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->limit(50)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.username')
                    ->label('Created by')
                    ->searchable(),
                IconColumn::make('is_group')
                    ->boolean()
                    ->label('Group'),
                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Started')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_group')
                    ->label('Group'),
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
