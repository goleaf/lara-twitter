<?php

namespace App\Filament\Resources\Hashtags;

use App\Filament\Resources\Hashtags\Pages\CreateHashtag;
use App\Filament\Resources\Hashtags\Pages\EditHashtag;
use App\Filament\Resources\Hashtags\Pages\ListHashtags;
use App\Filament\Resources\Hashtags\Schemas\HashtagForm;
use App\Filament\Resources\Hashtags\Tables\HashtagsTable;
use App\Models\Hashtag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HashtagResource extends Resource
{
    protected static ?string $model = Hashtag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['posts', 'reports']);
    }

    public static function form(Schema $schema): Schema
    {
        return HashtagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HashtagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHashtags::route('/'),
            'create' => CreateHashtag::route('/create'),
            'edit' => EditHashtag::route('/{record}/edit'),
        ];
    }
}
