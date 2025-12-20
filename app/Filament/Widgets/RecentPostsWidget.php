<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentPostsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->withoutGlobalScope('published')
                    ->with('user')
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('user.username')
                    ->label('Author')
                    ->searchable(),
                TextColumn::make('body')
                    ->limit(60)
                    ->wrap()
                    ->label('Post'),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Live'),
                TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->label('Scheduled')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10, 20])
            ->defaultPaginationPageOption(5);
    }
}
