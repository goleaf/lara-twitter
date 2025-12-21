<?php

namespace App\Filament\Resources\Moments;

use App\Filament\Resources\Moments\Pages\CreateMoment;
use App\Filament\Resources\Moments\Pages\EditMoment;
use App\Filament\Resources\Moments\Pages\ListMoments;
use App\Filament\Resources\Moments\Schemas\MomentForm;
use App\Filament\Resources\Moments\Tables\MomentsTable;
use App\Models\Moment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MomentResource extends Resource
{
    protected static ?string $model = Moment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return MomentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MomentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMoments::route('/'),
            'create' => CreateMoment::route('/create'),
            'edit' => EditMoment::route('/{record}/edit'),
        ];
    }
}
