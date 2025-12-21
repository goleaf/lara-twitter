<?php

namespace App\Filament\Resources\PostPollOptions\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostPollOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Option')
                    ->schema([
                        Select::make('post_poll_id')
                            ->relationship('poll', 'id')
                            ->searchable()
                            ->required(),
                        TextInput::make('option_text')
                            ->label('Option text')
                            ->maxLength(50)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
