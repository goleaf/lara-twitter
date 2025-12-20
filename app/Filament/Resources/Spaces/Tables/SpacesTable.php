<?php

namespace App\Filament\Resources\Spaces\Tables;

use App\Models\Space;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SpacesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('description')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('host.username')
                    ->label('Host')
                    ->searchable(),
                IconColumn::make('recording_enabled')
                    ->boolean()
                    ->label('Recording'),
                TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('participants_count')
                    ->counts('participants')
                    ->label('Participants')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('live')
                    ->label('Live now')
                    ->query(fn ($query) => $query->whereNotNull('started_at')->whereNull('ended_at')),
                Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(fn ($query) => $query->whereNotNull('scheduled_for')->whereNull('started_at')),
                Filter::make('ended')
                    ->label('Ended')
                    ->query(fn ($query) => $query->whereNotNull('ended_at')),
                TernaryFilter::make('recording_enabled')
                    ->label('Recording'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Space $record): string => route('spaces.show', $record))
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
