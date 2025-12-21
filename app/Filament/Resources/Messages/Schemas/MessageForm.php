<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
                    ->schema([
                        Select::make('conversation_id')
                            ->relationship('conversation', 'id')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        Textarea::make('body')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
