<?php

namespace App\Filament\Resources\MessageReactions;

use App\Filament\Resources\MessageReactions\Pages\CreateMessageReaction;
use App\Filament\Resources\MessageReactions\Pages\EditMessageReaction;
use App\Filament\Resources\MessageReactions\Pages\ListMessageReactions;
use App\Filament\Resources\MessageReactions\Schemas\MessageReactionForm;
use App\Filament\Resources\MessageReactions\Tables\MessageReactionsTable;
use App\Models\MessageReaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageReactionResource extends Resource
{
    protected static ?string $model = MessageReaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFaceSmile;

    protected static string|UnitEnum|null $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 50;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['message', 'user']);
    }

    public static function form(Schema $schema): Schema
    {
        return MessageReactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageReactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageReactions::route('/'),
            'create' => CreateMessageReaction::route('/create'),
            'edit' => EditMessageReaction::route('/{record}/edit'),
        ];
    }
}
