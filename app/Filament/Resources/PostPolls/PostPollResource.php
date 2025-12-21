<?php

namespace App\Filament\Resources\PostPolls;

use App\Filament\Resources\PostPolls\Pages\CreatePostPoll;
use App\Filament\Resources\PostPolls\Pages\EditPostPoll;
use App\Filament\Resources\PostPolls\Pages\ListPostPolls;
use App\Filament\Resources\PostPolls\Schemas\PostPollForm;
use App\Filament\Resources\PostPolls\Tables\PostPollsTable;
use App\Models\PostPoll;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostPollResource extends Resource
{
    protected static ?string $model = PostPoll::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 50;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['post' => fn ($query) => $query->withoutGlobalScope('published')->with('user')])
            ->withCount(['options', 'votes']);
    }

    public static function form(Schema $schema): Schema
    {
        return PostPollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostPollsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostPolls::route('/'),
            'create' => CreatePostPoll::route('/create'),
            'edit' => EditPostPoll::route('/{record}/edit'),
        ];
    }
}
