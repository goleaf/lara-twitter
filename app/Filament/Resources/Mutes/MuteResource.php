<?php

namespace App\Filament\Resources\Mutes;

use App\Filament\Resources\Mutes\Pages\CreateMute;
use App\Filament\Resources\Mutes\Pages\ListMutes;
use App\Filament\Resources\Mutes\Schemas\MuteForm;
use App\Filament\Resources\Mutes\Tables\MutesTable;
use App\Models\Mute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MuteResource extends Resource
{
    protected static ?string $model = Mute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSpeakerXMark;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 40;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['muter', 'muted']);
    }

    public static function form(Schema $schema): Schema
    {
        return MuteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MutesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMutes::route('/'),
            'create' => CreateMute::route('/create'),
        ];
    }
}
