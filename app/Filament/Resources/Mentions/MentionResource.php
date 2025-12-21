<?php

namespace App\Filament\Resources\Mentions;

use App\Filament\Resources\Mentions\Pages\CreateMention;
use App\Filament\Resources\Mentions\Pages\EditMention;
use App\Filament\Resources\Mentions\Pages\ListMentions;
use App\Filament\Resources\Mentions\Schemas\MentionForm;
use App\Filament\Resources\Mentions\Tables\MentionsTable;
use App\Models\Mention;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MentionResource extends Resource
{
    protected static ?string $model = Mention::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 25;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'post' => fn ($query) => $query->withoutGlobalScope('published'),
                'mentionedUser',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return MentionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMentions::route('/'),
            'create' => CreateMention::route('/create'),
            'edit' => EditMention::route('/{record}/edit'),
        ];
    }
}
