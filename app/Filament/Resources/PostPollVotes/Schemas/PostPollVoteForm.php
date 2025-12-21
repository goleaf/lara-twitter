<?php

namespace App\Filament\Resources\PostPollVotes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PostPollVoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vote')
                    ->schema([
                        Select::make('post_poll_id')
                            ->relationship('poll', 'id')
                            ->searchable()
                            ->required(),
                        Select::make('post_poll_option_id')
                            ->relationship('option', 'option_text')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
