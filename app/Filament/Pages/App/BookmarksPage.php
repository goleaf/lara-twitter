<?php

namespace App\Filament\Pages\App;

use App\Models\Bookmark;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\View as ViewLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookmarksPage extends Page implements HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $slug = 'bookmarks';
    protected static ?string $title = 'Bookmarks';
    protected static ?int $navigationSort = 50;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearAll')
                ->label('Clear all')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => Auth::check())
                ->action(function (): void {
                    abort_unless(Auth::check(), 403);

                    DB::table('bookmarks')->where('user_id', Auth::id())->delete();

                    $this->resetTable();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getBookmarksQuery())
            ->columns([
                ViewLayout::make('filament.app.tables.bookmark-row'),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-bookmark-slash')
                    ->visible(fn (): bool => Auth::check())
                    ->action(function (Bookmark $record): void {
                        abort_unless(Auth::check(), 403);

                        DB::table('bookmarks')
                            ->where('user_id', Auth::id())
                            ->where('post_id', $record->post_id)
                            ->delete();

                        $this->resetTable();
                    }),
            ], position: RecordActionsPosition::AfterContent)
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Remove selected')
                    ->icon('heroicon-o-bookmark-slash')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        abort_unless(Auth::check(), 403);

                        $postIds = $this->getSelectedTableRecords()->pluck('post_id')->filter()->unique()->values()->all();
                        if (! count($postIds)) {
                            return;
                        }

                        DB::table('bookmarks')
                            ->where('user_id', Auth::id())
                            ->whereIn('post_id', $postIds)
                            ->delete();

                        $this->deselectAllTableRecords();
                        $this->resetTable();
                    }),
            ])
            ->paginated([15, 30, 50])
            ->defaultPaginationPageOption(15);
    }

    private function getBookmarksQuery(): Builder
    {
        abort_unless(Auth::check(), 403);

        $query = Bookmark::query()
            ->where('bookmarks.user_id', Auth::id())
            ->leftJoin('posts', 'bookmarks.post_id', '=', 'posts.id')
            ->select('bookmarks.*')
            ->latest('bookmarks.created_at');

        $exclude = Auth::user()->excludedUserIds();
        if ($exclude->isNotEmpty()) {
            $query->where(function (Builder $q) use ($exclude): void {
                $q->whereNull('posts.id')->orWhereNotIn('posts.user_id', $exclude);
            });
        }

        return $query->with([
            'post' => fn ($q) => $q->with([
                'user',
                'images',
                'replyTo.user',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])->withCount(['likes', 'reposts', 'replies']),
        ]);
    }
}

