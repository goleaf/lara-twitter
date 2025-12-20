<?php

namespace App\Filament\Resources\MutedTerms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class MutedTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                IconColumn::make('whole_word')
                    ->boolean()
                    ->label('Whole word'),
                IconColumn::make('only_non_followed')
                    ->boolean()
                    ->label('Non-followed')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('mute_timeline')
                    ->boolean()
                    ->label('Timeline'),
                IconColumn::make('mute_notifications')
                    ->boolean()
                    ->label('Notifications'),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->label('Expires')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active')
                    ->query(fn ($query) => $query->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })),
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
