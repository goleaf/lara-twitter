<?php

namespace App\Filament\Resources\MutedTerms;

use App\Filament\Resources\MutedTerms\Pages\CreateMutedTerm;
use App\Filament\Resources\MutedTerms\Pages\EditMutedTerm;
use App\Filament\Resources\MutedTerms\Pages\ListMutedTerms;
use App\Filament\Resources\MutedTerms\Schemas\MutedTermForm;
use App\Filament\Resources\MutedTerms\Tables\MutedTermsTable;
use App\Models\MutedTerm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MutedTermResource extends Resource
{
    protected static ?string $model = MutedTerm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return MutedTermForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MutedTermsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMutedTerms::route('/'),
            'create' => CreateMutedTerm::route('/create'),
            'edit' => EditMutedTerm::route('/{record}/edit'),
        ];
    }
}
