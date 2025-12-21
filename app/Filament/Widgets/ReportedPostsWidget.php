<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ReportedPostsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->withoutGlobalScope('published')
                    ->select(['posts.id', 'posts.user_id', 'posts.body', 'posts.is_published', 'posts.created_at'])
                    ->with('user')
                    ->withCount('reports')
                    ->whereHas('reports')
                    ->orderByDesc('reports_count')
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('user.username')
                    ->label('Author')
                    ->searchable(),
                TextColumn::make('body')
                    ->label('Post')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->sortable(),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Live'),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created'),
            ])
            ->defaultSort('reports_count', 'desc')
            ->recordUrl(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Post $record): bool => (bool) $record->is_published)
                    ->url(fn (Post $record): string => route('posts.show', $record))
                    ->openUrlInNewTab(),
                Action::make('toggle-publish')
                    ->label(fn (Post $record): string => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (Post $record): string => $record->is_published ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (Post $record): string => $record->is_published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Post $record): bool => $record->update(['is_published' => ! $record->is_published])),
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 20])
            ->defaultPaginationPageOption(5);
    }
}
