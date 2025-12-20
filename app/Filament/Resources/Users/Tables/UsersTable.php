<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                IconColumn::make('is_admin')
                    ->boolean()
                    ->label('Admin'),
                IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),
                IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium'),
                IconColumn::make('analytics_enabled')
                    ->boolean()
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
