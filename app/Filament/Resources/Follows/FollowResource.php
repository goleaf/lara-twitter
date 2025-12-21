<?php

namespace App\Filament\Resources\Follows;

use App\Filament\Resources\Follows\Pages\CreateFollow;
use App\Filament\Resources\Follows\Pages\ListFollows;
use App\Filament\Resources\Follows\Schemas\FollowForm;
use App\Filament\Resources\Follows\Tables\FollowsTable;
use App\Models\Follow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FollowResource extends Resource
{
    protected static ?string $model = Follow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?int $navigationSort = 30;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['follower', 'followed']);
    }

    public static function form(Schema $schema): Schema
    {
        return FollowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FollowsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFollows::route('/'),
            'create' => CreateFollow::route('/create'),
        ];
    }
}
