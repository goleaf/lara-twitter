<?php

namespace App\Filament\Resources\Likes;

use App\Filament\Resources\Likes\Pages\CreateLike;
use App\Filament\Resources\Likes\Pages\ListLikes;
use App\Filament\Resources\Likes\Schemas\LikeForm;
use App\Filament\Resources\Likes\Tables\LikesTable;
use App\Models\Like;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LikeResource extends Resource
{
    protected static ?string $model = Like::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?int $navigationSort = 50;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user',
                'post' => fn ($query) => $query->withoutGlobalScope('published'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return LikeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LikesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLikes::route('/'),
            'create' => CreateLike::route('/create'),
        ];
    }
}
