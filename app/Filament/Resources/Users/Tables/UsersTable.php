<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->searchable()
                    ->label('Handle')
                    ->prefix('@')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('avatar_path')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->sortable(),
                TextColumn::make('followers_count')
                    ->label('Followers')
                    ->sortable(),
                TextColumn::make('following_count')
                    ->label('Following')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reports_made_count')
                    ->label('Reports filed')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_admin')
                    ->label('Admin')
                    ->disabled(fn (User $record): bool => $record->id === auth()->id()),
                ToggleColumn::make('is_verified')
                    ->label('Verified'),
                ToggleColumn::make('is_premium')
                    ->label('Premium'),
                ToggleColumn::make('analytics_enabled')
                    ->label('Analytics')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Joined')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Admin'),
                TernaryFilter::make('is_verified')
                    ->label('Verified'),
                TernaryFilter::make('is_premium')
                    ->label('Premium'),
                TernaryFilter::make('analytics_enabled')
                    ->label('Analytics'),
                Filter::make('email_verified')
                    ->label('Email verified')
                    ->query(fn ($query) => $query->whereNotNull('email_verified_at')),
                Filter::make('reported')
                    ->label('Has reports')
                    ->query(fn ($query) => $query->whereHas('reports')),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record): string => route('profile.show', ['user' => $record->username]))
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
