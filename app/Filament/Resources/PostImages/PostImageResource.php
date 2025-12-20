<?php

namespace App\Filament\Resources\PostImages;

use App\Filament\Resources\PostImages\Pages\CreatePostImage;
use App\Filament\Resources\PostImages\Pages\EditPostImage;
use App\Filament\Resources\PostImages\Pages\ListPostImages;
use App\Filament\Resources\PostImages\Schemas\PostImageForm;
use App\Filament\Resources\PostImages\Tables\PostImagesTable;
use App\Models\PostImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostImageResource extends Resource
{
    protected static ?string $model = PostImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return PostImageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostImagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostImages::route('/'),
            'create' => CreatePostImage::route('/create'),
            'edit' => EditPostImage::route('/{record}/edit'),
        ];
    }
}
