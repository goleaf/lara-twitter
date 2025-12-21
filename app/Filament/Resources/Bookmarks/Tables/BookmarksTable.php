<?php

namespace App\Filament\Resources\Bookmarks\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Bookmark;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class BookmarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('post.body')
                    ->label('Post body')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'username'),
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
            ])
            ->recordActions([
                Action::make('view-user')
                    ->label('User')
                    ->icon('heroicon-o-user')
                    ->url(fn (Bookmark $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Bookmark $record): string => PostResource::getUrl('edit', ['record' => $record->post_id]))
                    ->openUrlInNewTab(),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Bookmark $record): void {
                        Bookmark::query()
                            ->where('user_id', $record->user_id)
                            ->where('post_id', $record->post_id)
                            ->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Bookmark $record): void {
                                Bookmark::query()
                                    ->where('user_id', $record->user_id)
                                    ->where('post_id', $record->post_id)
                                    ->delete();
                            });
                        }),
                ]),
            ])
            ->defaultKeySort(false)
            ->defaultSort('created_at', 'desc');
    }
}
