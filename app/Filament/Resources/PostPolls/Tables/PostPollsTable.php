<?php

namespace App\Filament\Resources\PostPolls\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Models\PostPoll;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PostPollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('post.user.username')
                    ->label('Author')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('post.body')
                    ->label('Post body')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PostPoll $record): string => PostResource::getUrl('edit', ['record' => $record->post_id]))
                    ->openUrlInNewTab(),
                Action::make('end-poll')
                    ->label('End now')
                    ->icon('heroicon-o-stop-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (PostPoll $record): bool => (bool) $record->ends_at?->isFuture())
                    ->action(fn (PostPoll $record): bool => $record->update(['ends_at' => now()])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('end-now')
                        ->label('End now')
                        ->icon('heroicon-o-stop-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $timestamp = now();

                            PostPoll::query()
                                ->whereKey($records->modelKeys())
                                ->where(function ($query) use ($timestamp): void {
                                    $query->whereNull('ends_at')
                                        ->orWhere('ends_at', '>', $timestamp);
                                })
                                ->update(['ends_at' => $timestamp]);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
