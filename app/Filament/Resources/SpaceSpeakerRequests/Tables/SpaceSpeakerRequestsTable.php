<?php

namespace App\Filament\Resources\SpaceSpeakerRequests\Tables;

use App\Models\SpaceSpeakerRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpaceSpeakerRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('space.title')
                    ->label('Space')
                    ->searchable(),
                TextColumn::make('user.username')
                    ->label('Requester')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('decidedBy.username')
                    ->label('Decided by')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('decided_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Requested')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(SpaceSpeakerRequest::statuses(), SpaceSpeakerRequest::statuses())),
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
