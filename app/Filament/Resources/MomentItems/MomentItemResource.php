<?php

namespace App\Filament\Resources\MomentItems;

use App\Filament\Resources\MomentItems\Pages\CreateMomentItem;
use App\Filament\Resources\MomentItems\Pages\EditMomentItem;
use App\Filament\Resources\MomentItems\Pages\ListMomentItems;
use App\Filament\Resources\MomentItems\Schemas\MomentItemForm;
use App\Filament\Resources\MomentItems\Tables\MomentItemsTable;
use App\Models\MomentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MomentItemResource extends Resource
{
    protected static ?string $model = MomentItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 40;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['moment']);
    }

    public static function form(Schema $schema): Schema
    {
        return MomentItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MomentItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMomentItems::route('/'),
            'create' => CreateMomentItem::route('/create'),
            'edit' => EditMomentItem::route('/{record}/edit'),
        ];
    }
}
