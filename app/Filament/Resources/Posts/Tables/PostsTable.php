<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Live')
                    ->sortable(),
                TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->label('Scheduled')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reply_policy')
                    ->label('Reply policy')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_reply_like')
                    ->boolean()
                    ->label('Reply-like')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('likes_count')
                    ->counts('likes')
                    ->label('Likes')
                    ->sortable(),
                TextColumn::make('reposts_count')
                    ->counts('reposts')
                    ->label('Reposts')
                    ->sortable(),
                TextColumn::make('replies_count')
                    ->counts('replies')
                    ->label('Replies')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reports_count')
                    ->counts('reports')
                    ->label('Reports')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reply_to_id')
                    ->label('Reply to')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('repost_of_id')
                    ->label('Repost of')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Live'),
                TernaryFilter::make('is_reply_like')
                    ->label('Reply-like'),
                Filter::make('scheduled')
                    ->label('Scheduled')
                    ->query(fn ($query) => $query->whereNotNull('scheduled_for')),
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
                Filter::make('has_images')
                    ->label('Has images')
                    ->query(fn ($query) => $query->whereHas('images')),
                Filter::make('has_link_preview')
                    ->label('Has link preview')
                    ->query(fn ($query) => $query->whereHas('linkPreview')),
                Filter::make('has_poll')
                    ->label('Has poll')
                    ->query(fn ($query) => $query->whereHas('poll')),
                Filter::make('is_reply')
                    ->label('Is reply')
                    ->query(fn ($query) => $query->whereNotNull('reply_to_id')),
                SelectFilter::make('reply_policy')
                    ->label('Reply policy')
                    ->options(array_combine(Post::replyPolicies(), Post::replyPolicies())),
            ])
            ->recordActions([
                Action::make('view-author')
                    ->label('Author')
                    ->icon('heroicon-o-user')
                    ->url(fn (Post $record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Post $record): string => route('posts.show', $record))
                    ->visible(fn (Post $record): bool => (bool) $record->is_published)
                    ->openUrlInNewTab(),
                Action::make('toggle-publish')
                    ->label(fn (Post $record): string => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (Post $record): string => $record->is_published ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (Post $record): string => $record->is_published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Post $record) => $record->update(['is_published' => ! $record->is_published])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            Post::query()
                                ->withoutGlobalScope('published')
                                ->whereKey($records->modelKeys())
                                ->update(['is_published' => true]);
                        }),
                    BulkAction::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-lock-closed')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            Post::query()
                                ->withoutGlobalScope('published')
                                ->whereKey($records->modelKeys())
                                ->update(['is_published' => false]);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
