<?php

namespace App\Filament\Resources\MessageReactions\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MessageReactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reaction')
                    ->schema([
                        Select::make('message_id')
                            ->relationship('message', 'id')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        TextInput::make('emoji')
                            ->maxLength(32)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
