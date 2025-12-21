<?php

namespace App\Filament\Resources\PostPollVotes;

use App\Filament\Resources\PostPollVotes\Pages\CreatePostPollVote;
use App\Filament\Resources\PostPollVotes\Pages\EditPostPollVote;
use App\Filament\Resources\PostPollVotes\Pages\ListPostPollVotes;
use App\Filament\Resources\PostPollVotes\Schemas\PostPollVoteForm;
use App\Filament\Resources\PostPollVotes\Tables\PostPollVotesTable;
use App\Models\PostPollVote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostPollVoteResource extends Resource
{
    protected static ?string $model = PostPollVote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 80;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['poll', 'option', 'user']);
    }

    public static function form(Schema $schema): Schema
    {
        return PostPollVoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostPollVotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostPollVotes::route('/'),
            'create' => CreatePostPollVote::route('/create'),
            'edit' => EditPostPollVote::route('/{record}/edit'),
        ];
    }
}
