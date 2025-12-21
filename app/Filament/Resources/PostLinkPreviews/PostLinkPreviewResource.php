<?php

namespace App\Filament\Resources\PostLinkPreviews;

use App\Filament\Resources\PostLinkPreviews\Pages\CreatePostLinkPreview;
use App\Filament\Resources\PostLinkPreviews\Pages\EditPostLinkPreview;
use App\Filament\Resources\PostLinkPreviews\Pages\ListPostLinkPreviews;
use App\Filament\Resources\PostLinkPreviews\Schemas\PostLinkPreviewForm;
use App\Filament\Resources\PostLinkPreviews\Tables\PostLinkPreviewsTable;
use App\Models\PostLinkPreview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostLinkPreviewResource extends Resource
{
    protected static ?string $model = PostLinkPreview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 90;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['post' => fn ($query) => $query->withoutGlobalScope('published')]);
    }

    public static function form(Schema $schema): Schema
    {
        return PostLinkPreviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostLinkPreviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostLinkPreviews::route('/'),
            'create' => CreatePostLinkPreview::route('/create'),
            'edit' => EditPostLinkPreview::route('/{record}/edit'),
        ];
    }
}
