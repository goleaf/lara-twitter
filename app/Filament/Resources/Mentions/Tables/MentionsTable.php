<?php

namespace App\Filament\Resources\Mentions\Tables;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Mention;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MentionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post_id')
                    ->label('Post')
                    ->sortable(),
                TextColumn::make('post.body')
                    ->label('Post body')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mentionedUser.username')
                    ->label('Mentioned')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published')),
                SelectFilter::make('mentioned_user_id')
                    ->label('Mentioned user')
                    ->relationship('mentionedUser', 'username'),
            ])
            ->recordActions([
                Action::make('view-post')
                    ->label('Post')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Mention $record): string => PostResource::getUrl('edit', ['record' => $record->post_id]))
                    ->openUrlInNewTab(),
                Action::make('view-mentioned')
                    ->label('Mentioned')
                    ->icon('heroicon-o-user')
                    ->url(fn (Mention $record): string => UserResource::getUrl('edit', ['record' => $record->mentioned_user_id]))
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
