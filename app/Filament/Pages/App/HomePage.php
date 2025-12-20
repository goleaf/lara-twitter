<?php

namespace App\Filament\Pages\App;

use App\Models\Post;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HomePage extends Page implements HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = '';
    protected static ?string $title = 'Home';

    protected static string $view = 'filament-panels::pages.page';

    /**
     * @var array<string, mixed>
     */
    public array $composer = [];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('composer')->visible(fn (): bool => Auth::check()),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->timelineQuery())
            ->contentGrid([
                'default' => 1,
            ])
            ->paginated([15, 30, 50])
            ->defaultPaginationPageOption(15);
    }

    private function timelineQuery(): Builder
    {
        $query = Post::query()
            ->with(['user', 'images'])
            ->withCount(['likes', 'reposts', 'replies'])
            ->whereNull('reply_to_id')
            ->latest();

        if (Auth::check()) {
            $followingIds = Auth::user()->following()->pluck('users.id')->push(Auth::id());
            $query->whereIn('user_id', $followingIds);
        }

        return $query;
    }
}

