<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Filament\Resources\Hashtags\HashtagResource;
use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Spaces\SpaceResource;
use App\Filament\Resources\UserLists\UserListResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Hashtag;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use App\Models\UserList;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('case_number')
                    ->label('Case')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reason')
                    ->formatStateUsing(fn (?string $state): string => $state ? Report::reasonLabel($state) : '')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reporter.username')
                    ->label('Reporter')
                    ->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '')
                    ->sortable(),
                TextColumn::make('reportable_id')
                    ->label('Target ID')
                    ->sortable(),
                TextColumn::make('reportable_summary')
                    ->label('Target')
                    ->getStateUsing(fn (Report $record): string => self::reportableLabel($record))
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Filed')
                    ->sortable(),
                TextColumn::make('resolvedBy.username')
                    ->label('Resolved By')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resolved_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(Report::statuses(), Report::statuses())),
                SelectFilter::make('reason')
                    ->options(Report::reasonLabels()),
                SelectFilter::make('reportable_type')
                    ->label('Target type')
                    ->options([
                        'App\\Models\\Post' => 'Post',
                        'App\\Models\\Hashtag' => 'Hashtag',
                        'App\\Models\\Space' => 'Space',
                        'App\\Models\\Message' => 'Message',
                        'App\\Models\\User' => 'User',
                        'App\\Models\\UserList' => 'List',
                    ]),
            ])
            ->recordActions([
                Action::make('view-target')
                    ->label('Target')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Report $record): ?string => self::reportableAdminUrl($record))
                    ->visible(fn (Report $record): bool => filled(self::reportableAdminUrl($record)))
                    ->openUrlInNewTab(),
                Action::make('view-reporter')
                    ->label('Reporter')
                    ->icon('heroicon-o-user')
                    ->url(fn (Report $record): ?string => $record->reporter_id
                        ? UserResource::getUrl('edit', ['record' => $record->reporter_id])
                        : null)
                    ->visible(fn (Report $record): bool => (bool) $record->reporter_id)
                    ->openUrlInNewTab(),
                Action::make('resolve-unpublish-post')
                    ->label('Resolve & unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->reportable instanceof Post && $record->reportable->is_published)
                    ->action(function (Report $record): void {
                        $record->loadMissing('reportable');

                        $post = $record->reportable;

                        if (! $post instanceof Post) {
                            return;
                        }

                        $post->update(['is_published' => false]);
                        $record->update(['status' => Report::STATUS_RESOLVED]);
                    }),
                Action::make('resolve-delete-message')
                    ->label('Resolve & delete message')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->reportable instanceof Message && ! $record->reportable->trashed())
                    ->action(function (Report $record): void {
                        $record->loadMissing('reportable');

                        $message = $record->reportable;

                        if (! $message instanceof Message) {
                            return;
                        }

                        $message->delete();
                        $record->update(['status' => Report::STATUS_RESOLVED]);
                    }),
                Action::make('resolve-end-space')
                    ->label('Resolve & end space')
                    ->icon('heroicon-o-stop-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->reportable instanceof Space && ! $record->reportable->ended_at)
                    ->action(function (Report $record): void {
                        $record->loadMissing('reportable');

                        $space = $record->reportable;

                        if (! $space instanceof Space) {
                            return;
                        }

                        $space->update(['ended_at' => now()]);
                        $record->update(['status' => Report::STATUS_RESOLVED]);
                    }),
                Action::make('resolve-delete-hashtag')
                    ->label('Resolve & delete hashtag')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->reportable instanceof Hashtag)
                    ->action(function (Report $record): void {
                        $record->loadMissing('reportable');

                        $hashtag = $record->reportable;

                        if (! $hashtag instanceof Hashtag) {
                            return;
                        }

                        $hashtag->delete();
                        $record->update(['status' => Report::STATUS_RESOLVED]);
                    }),
                Action::make('resolve-delete-list')
                    ->label('Resolve & delete list')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->reportable instanceof UserList)
                    ->action(function (Report $record): void {
                        $record->loadMissing('reportable');

                        $list = $record->reportable;

                        if (! $list instanceof UserList) {
                            return;
                        }

                        $list->delete();
                        $record->update(['status' => Report::STATUS_RESOLVED]);
                    }),
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Report $record): bool => $record->status === Report::STATUS_OPEN)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_REVIEWING])),
                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_RESOLVED)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_RESOLVED])),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->status !== Report::STATUS_DISMISSED)
                    ->action(fn (Report $record) => $record->update(['status' => Report::STATUS_DISMISSED])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark-reviewing')
                        ->label('Mark reviewing')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each(
                            fn (Report $record) => $record->update(['status' => Report::STATUS_REVIEWING])
                        )),
                    BulkAction::make('mark-resolved')
                        ->label('Mark resolved')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn (Report $record) => $record->update(['status' => Report::STATUS_RESOLVED])
                        )),
                    BulkAction::make('mark-dismissed')
                        ->label('Mark dismissed')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn (Report $record) => $record->update(['status' => Report::STATUS_DISMISSED])
                        )),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function reportableLabel(Report $record): string
    {
        $reportable = $record->reportable;

        if (! $reportable) {
            $type = $record->reportable_type ? class_basename($record->reportable_type) : 'Unknown';
            $suffix = $record->reportable_id ? ' #' . $record->reportable_id : '';

            return $type . $suffix;
        }

        return match (true) {
            $reportable instanceof Post => Str::limit((string) $reportable->body, 60, '...') ?: 'Post #' . $reportable->getKey(),
            $reportable instanceof Message => Str::limit((string) $reportable->body, 60, '...') ?: 'Message #' . $reportable->getKey(),
            $reportable instanceof User => '@' . $reportable->username,
            $reportable instanceof Space => ($reportable->title ?: 'Space #' . $reportable->getKey()),
            $reportable instanceof Hashtag => '#' . $reportable->tag,
            $reportable instanceof UserList => ($reportable->name ?: 'List #' . $reportable->getKey()),
            default => class_basename($reportable) . ' #' . $reportable->getKey(),
        };
    }

    private static function reportableAdminUrl(Report $record): ?string
    {
        if (! $record->reportable_type || ! $record->reportable_id) {
            return null;
        }

        return match ($record->reportable_type) {
            Post::class => PostResource::getUrl('edit', ['record' => $record->reportable_id]),
            Message::class => MessageResource::getUrl('edit', ['record' => $record->reportable_id]),
            Space::class => SpaceResource::getUrl('edit', ['record' => $record->reportable_id]),
            User::class => UserResource::getUrl('edit', ['record' => $record->reportable_id]),
            Hashtag::class => HashtagResource::getUrl('edit', ['record' => $record->reportable_id]),
            UserList::class => UserListResource::getUrl('edit', ['record' => $record->reportable_id]),
            default => null,
        };
    }
}
