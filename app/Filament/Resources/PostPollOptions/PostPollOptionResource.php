<?php

namespace App\Filament\Resources\PostPollOptions;

use App\Filament\Resources\PostPollOptions\Pages\CreatePostPollOption;
use App\Filament\Resources\PostPollOptions\Pages\EditPostPollOption;
use App\Filament\Resources\PostPollOptions\Pages\ListPostPollOptions;
use App\Filament\Resources\PostPollOptions\Schemas\PostPollOptionForm;
use App\Filament\Resources\PostPollOptions\Tables\PostPollOptionsTable;
use App\Models\PostPollOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostPollOptionResource extends Resource
{
    protected static ?string $model = PostPollOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 70;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with('poll')
            ->withCount('votes');
    }

    public static function form(Schema $schema): Schema
    {
        return PostPollOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostPollOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostPollOptions::route('/'),
            'create' => CreatePostPollOption::route('/create'),
            'edit' => EditPostPollOption::route('/{record}/edit'),
        ];
    }
}
