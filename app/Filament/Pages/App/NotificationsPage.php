<?php

namespace App\Filament\Pages\App;

use App\Models\User;
use App\Services\NotificationVisibilityService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\View as ViewLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationsPage extends Page implements HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $slug = 'notifications';
    protected static ?string $title = 'Notifications';
    protected static ?int $navigationSort = 30;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getNotificationsQuery())
            ->columns([
                ViewLayout::make('filament.app.tables.notification-row'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (DatabaseNotification $record): bool => filled($this->notificationUrl($record)))
                    ->url(fn (DatabaseNotification $record): string => $this->notificationUrl($record) ?? '#'),
                Action::make('markRead')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->visible(fn (DatabaseNotification $record): bool => $record->read_at === null)
                    ->action(function (DatabaseNotification $record): void {
                        $record->markAsRead();
                        $this->resetTable();
                    }),
            ], position: RecordActionsPosition::AfterContent)
            ->bulkActions([
                BulkAction::make('markAllRead')
                    ->label('Mark selected as read')
                    ->icon('heroicon-o-check')
                    ->action(function (): void {
                        abort_unless(Auth::check(), 403);

                        $ids = $this->getSelectedTableRecords()->pluck('id')->all();
                        if (! count($ids)) {
                            return;
                        }

                        Auth::user()
                            ->notifications()
                            ->whereIn('id', $ids)
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);

                        $this->deselectAllTableRecords();
                        $this->resetTable();
                    }),
                BulkAction::make('delete')
                    ->label('Delete selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        abort_unless(Auth::check(), 403);

                        $ids = $this->getSelectedTableRecords()->pluck('id')->all();
                        if (! count($ids)) {
                            return;
                        }

                        Auth::user()
                            ->notifications()
                            ->whereIn('id', $ids)
                            ->delete();

                        $this->deselectAllTableRecords();
                        $this->resetTable();
                    }),
            ])
            ->filters([
                Filter::make('unread')
                    ->label('Unread only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('read_at'))
                    ->toggle(),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->groups([
                Group::make('type')
                    ->label('Notification Type')
                    ->collapsible(),
                Group::make('created_at')
                    ->label('Date')
                    ->date()
                    ->collapsible(),
            ])
            ->defaultGroup('created_at')
            ->paginated([20, 50, 100])
            ->defaultPaginationPageOption(20)
            ->poll('15s');
    }

    private function getNotificationsQuery(): Builder
    {
        abort_unless(Auth::check(), 403);

        $items = Auth::user()
            ->notifications()
            ->latest()
            ->limit(200)
            ->get();

        $items = app(NotificationVisibilityService::class)->filter(Auth::user(), $items);

        if ($this->currentTab() === 'verified') {
            $items = $this->applyVerifiedFilter($items);
        }

        $ids = $items->pluck('id')->all();

        return DatabaseNotification::query()
            ->whereIn('id', $ids ?: [-1])
            ->orderByRaw('case id ' . $this->orderCaseSql($ids) . ' end');
    }

    private function currentTab(): string
    {
        $tab = request()->query('tab', 'all');

        return in_array($tab, ['all', 'verified'], true) ? $tab : 'all';
    }

    private function applyVerifiedFilter(Collection $items): Collection
    {
        $actorIds = $items
            ->map(fn ($n) => $this->actorUserId($n->data ?? []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $verifiedIds = User::query()
            ->whereIn('id', $actorIds ?: [-1])
            ->where('is_verified', true)
            ->pluck('id')
            ->all();

        return $items
            ->filter(function ($n) use ($verifiedIds) {
                $id = $this->actorUserId($n->data ?? []);

                return $id && in_array($id, $verifiedIds, true);
            })
            ->values();
    }

    private function actorUserId(array $data): ?int
    {
        $id = Arr::get($data, 'actor_user_id');

        return is_numeric($id) ? (int) $id : null;
    }

    private function orderCaseSql(array $ids): string
    {
        $sql = '';

        foreach (array_values($ids) as $index => $id) {
            $escapedId = str_replace("'", "''", (string) $id);
            $sql .= "when '{$escapedId}' then {$index} ";
        }

        return $sql === '' ? 'when id then 0' : $sql;
    }

    private function notificationUrl(DatabaseNotification $notification): ?string
    {
        $data = $notification->data ?? [];
        $type = $data['type'] ?? null;

        $postId = $data['post_id'] ?? $data['original_post_id'] ?? null;
        $conversationId = $data['conversation_id'] ?? null;
        $profileUsername = $data['follower_username'] ?? $data['actor_username'] ?? null;

        if ($type === 'message_received' && $conversationId) {
            return route('messages.show', $conversationId);
        }

        if ($type === 'user_followed' && $profileUsername) {
            return route('profile.show', ['user' => $profileUsername]);
        }

        if ($type === 'added_to_list' && ($data['list_id'] ?? null)) {
            return route('lists.show', $data['list_id']);
        }

        if ($postId) {
            return route('posts.show', $postId);
        }

        return null;
    }

    public static function actorUsername(DatabaseNotification $notification): string
    {
        $data = $notification->data ?? [];

        return (string) ($data['actor_username']
            ?? $data['follower_username']
            ?? $data['sender_username']
            ?? 'someone');
    }

    public static function actorUser(DatabaseNotification $notification): ?User
    {
        $data = $notification->data ?? [];
        $id = Arr::get($data, 'actor_user_id');
        $id = is_numeric($id) ? (int) $id : null;

        if (! $id) {
            return null;
        }

        return User::query()->find($id);
    }
}
