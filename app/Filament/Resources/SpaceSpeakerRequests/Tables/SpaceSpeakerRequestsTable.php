<?php

namespace App\Filament\Resources\SpaceSpeakerRequests\Tables;

use App\Filament\Resources\Spaces\SpaceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\SpaceSpeakerRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
                Action::make('view-space')
                    ->label('Space')
                    ->icon('heroicon-o-microphone')
                    ->url(fn (SpaceSpeakerRequest $record): string => SpaceResource::getUrl('edit', ['record' => $record->space_id]))
                    ->openUrlInNewTab(),
                Action::make('view-requester')
                    ->label('Requester')
                    ->icon('heroicon-o-user')
                    ->url(fn (SpaceSpeakerRequest $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                Action::make('view-decider')
                    ->label('Decided by')
                    ->icon('heroicon-o-user')
                    ->visible(fn (SpaceSpeakerRequest $record): bool => (bool) $record->decided_by)
                    ->url(fn (SpaceSpeakerRequest $record): string => UserResource::getUrl('edit', ['record' => $record->decided_by]))
                    ->openUrlInNewTab(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SpaceSpeakerRequest $record): bool => $record->status === SpaceSpeakerRequest::STATUS_PENDING)
                    ->action(fn (SpaceSpeakerRequest $record): bool => $record->update([
                        'status' => SpaceSpeakerRequest::STATUS_APPROVED,
                        'decided_by' => auth()->id(),
                        'decided_at' => now(),
                    ])),
                Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SpaceSpeakerRequest $record): bool => $record->status === SpaceSpeakerRequest::STATUS_PENDING)
                    ->action(fn (SpaceSpeakerRequest $record): bool => $record->update([
                        'status' => SpaceSpeakerRequest::STATUS_DENIED,
                        'decided_by' => auth()->id(),
                        'decided_at' => now(),
                    ])),
                Action::make('reset-pending')
                    ->label('Reset to pending')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (SpaceSpeakerRequest $record): bool => $record->status !== SpaceSpeakerRequest::STATUS_PENDING)
                    ->action(fn (SpaceSpeakerRequest $record): bool => $record->update([
                        'status' => SpaceSpeakerRequest::STATUS_PENDING,
                        'decided_by' => null,
                        'decided_at' => null,
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $timestamp = now();

                            SpaceSpeakerRequest::query()
                                ->whereKey($records->modelKeys())
                                ->update([
                                    'status' => SpaceSpeakerRequest::STATUS_APPROVED,
                                    'decided_by' => auth()->id(),
                                    'decided_at' => $timestamp,
                                ]);
                        }),
                    BulkAction::make('deny')
                        ->label('Deny')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $timestamp = now();

                            SpaceSpeakerRequest::query()
                                ->whereKey($records->modelKeys())
                                ->update([
                                    'status' => SpaceSpeakerRequest::STATUS_DENIED,
                                    'decided_by' => auth()->id(),
                                    'decided_at' => $timestamp,
                                ]);
                        }),
                    BulkAction::make('reset-pending')
                        ->label('Reset to pending')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => SpaceSpeakerRequest::query()
                            ->whereKey($records->modelKeys())
                            ->update([
                                'status' => SpaceSpeakerRequest::STATUS_PENDING,
                                'decided_by' => null,
                                'decided_at' => null,
                            ])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
