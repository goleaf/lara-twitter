<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('case_number')
                    ->label('Case'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reason')
                    ->formatStateUsing(fn (?string $state): string => $state ? Report::reasonLabel($state) : '')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reporter.username')
                    ->label('Reporter')
                    ->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable(),
                TextColumn::make('reportable_id')
                    ->label('Target ID')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('resolvedBy.username')
                    ->label('Resolved By'),
                TextColumn::make('resolved_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'open',
                        'reviewing' => 'reviewing',
                        'resolved' => 'resolved',
                        'dismissed' => 'dismissed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
