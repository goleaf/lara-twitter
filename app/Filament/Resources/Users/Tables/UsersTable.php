<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
                Action::make('verify-email')
                    ->label('Verify email')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! $record->email_verified_at)
                    ->action(function (User $record): bool {
                        $record->forceFill(['email_verified_at' => now()]);

                        return $record->save();
                    }),
                Action::make('clear-email-verification')
                    ->label('Clear email')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => (bool) $record->email_verified_at)
                    ->action(function (User $record): bool {
                        $record->forceFill(['email_verified_at' => null]);

                        return $record->save();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark-verified')
                        ->label('Mark verified')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['is_verified' => true])),
                    BulkAction::make('mark-unverified')
                        ->label('Remove verified')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['is_verified' => false])),
                    BulkAction::make('mark-premium')
                        ->label('Mark premium')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['is_premium' => true])),
                    BulkAction::make('remove-premium')
                        ->label('Remove premium')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['is_premium' => false])),
                    BulkAction::make('enable-analytics')
                        ->label('Enable analytics')
                        ->icon('heroicon-o-chart-bar')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['analytics_enabled' => true])),
                    BulkAction::make('disable-analytics')
                        ->label('Disable analytics')
                        ->icon('heroicon-o-chart-bar')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['analytics_enabled' => false])),
                    BulkAction::make('grant-admin')
                        ->label('Grant admin')
                        ->icon('heroicon-o-shield-check')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['is_admin' => true])),
                    BulkAction::make('revoke-admin')
                        ->label('Revoke admin')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->where('id', '!=', auth()->id())
                            ->update(['is_admin' => false])),
                    BulkAction::make('verify-email')
                        ->label('Verify email')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): int {
                            $timestamp = now();

                            return User::query()
                                ->whereKey($records->modelKeys())
                                ->whereNull('email_verified_at')
                                ->update(['email_verified_at' => $timestamp]);
                        }),
                    BulkAction::make('clear-email-verification')
                        ->label('Clear email')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records): int => User::query()
                            ->whereKey($records->modelKeys())
                            ->update(['email_verified_at' => null])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
