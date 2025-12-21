<?php

namespace App\Filament\Resources\UserLists\Tables;

use App\Models\UserList;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserListsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner.username')
                    ->label('Owner')
                    ->searchable(),
                IconColumn::make('is_private')
                    ->boolean()
                    ->label('Private'),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable(),
                TextColumn::make('subscribers_count')
                    ->label('Subscribers')
                    ->sortable(),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_private')
                    ->label('Private'),
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (UserList $record): string => route('lists.show', $record))
                    ->openUrlInNewTab(),
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
