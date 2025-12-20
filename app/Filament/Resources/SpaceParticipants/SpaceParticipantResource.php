<?php

namespace App\Filament\Resources\SpaceParticipants;

use App\Filament\Resources\SpaceParticipants\Pages\CreateSpaceParticipant;
use App\Filament\Resources\SpaceParticipants\Pages\EditSpaceParticipant;
use App\Filament\Resources\SpaceParticipants\Pages\ListSpaceParticipants;
use App\Filament\Resources\SpaceParticipants\Schemas\SpaceParticipantForm;
use App\Filament\Resources\SpaceParticipants\Tables\SpaceParticipantsTable;
use App\Models\SpaceParticipant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpaceParticipantResource extends Resource
{
    protected static ?string $model = SpaceParticipant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Spaces';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return SpaceParticipantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpaceParticipantsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpaceParticipants::route('/'),
            'create' => CreateSpaceParticipant::route('/create'),
            'edit' => EditSpaceParticipant::route('/{record}/edit'),
        ];
    }
}
