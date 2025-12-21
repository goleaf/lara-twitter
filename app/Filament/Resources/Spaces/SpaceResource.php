<?php

namespace App\Filament\Resources\Spaces;

use App\Filament\Resources\Spaces\Pages\CreateSpace;
use App\Filament\Resources\Spaces\Pages\EditSpace;
use App\Filament\Resources\Spaces\Pages\ListSpaces;
use App\Filament\Resources\Spaces\Schemas\SpaceForm;
use App\Filament\Resources\Spaces\Tables\SpacesTable;
use App\Models\Space;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpaceResource extends Resource
{
    protected static ?string $model = Space::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string|UnitEnum|null $navigationGroup = 'Spaces';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return SpaceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpacesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpaces::route('/'),
            'create' => CreateSpace::route('/create'),
            'edit' => EditSpace::route('/{record}/edit'),
        ];
    }
}
