<?php

namespace App\Filament\Resources\SpaceReactions;

use App\Filament\Resources\SpaceReactions\Pages\CreateSpaceReaction;
use App\Filament\Resources\SpaceReactions\Pages\EditSpaceReaction;
use App\Filament\Resources\SpaceReactions\Pages\ListSpaceReactions;
use App\Filament\Resources\SpaceReactions\Schemas\SpaceReactionForm;
use App\Filament\Resources\SpaceReactions\Tables\SpaceReactionsTable;
use App\Models\SpaceReaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpaceReactionResource extends Resource
{
    protected static ?string $model = SpaceReaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Spaces';

    protected static ?int $navigationSort = 40;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['space', 'user']);
    }

    public static function form(Schema $schema): Schema
    {
        return SpaceReactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpaceReactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpaceReactions::route('/'),
            'create' => CreateSpaceReaction::route('/create'),
            'edit' => EditSpaceReaction::route('/{record}/edit'),
        ];
    }
}
