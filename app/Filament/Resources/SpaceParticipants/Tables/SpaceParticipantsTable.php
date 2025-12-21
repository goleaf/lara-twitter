<?php

namespace App\Filament\Resources\SpaceParticipants\Tables;

use App\Filament\Resources\Spaces\SpaceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\SpaceParticipant;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpaceParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('space.title')
                    ->label('Space')
                    ->searchable(),
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('role')
                    ->sortable(),
                TextColumn::make('joined_at')
                    ->dateTime()
                    ->label('Joined')
                    ->sortable(),
                TextColumn::make('left_at')
                    ->dateTime()
                    ->label('Left')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'host' => 'Host',
                        'speaker' => 'Speaker',
                        'listener' => 'Listener',
                    ]),
            ])
            ->recordActions([
                Action::make('view-space')
                    ->label('Space')
                    ->icon('heroicon-o-microphone')
                    ->url(fn (SpaceParticipant $record): string => SpaceResource::getUrl('edit', ['record' => $record->space_id]))
                    ->openUrlInNewTab(),
                Action::make('view-user')
                    ->label('User')
                    ->icon('heroicon-o-user')
                    ->url(fn (SpaceParticipant $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
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
