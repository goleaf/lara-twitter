<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation')
                    ->schema([
                        Select::make('created_by_user_id')
                            ->relationship('createdBy', 'username')
                            ->searchable()
                            ->required(),
                        Toggle::make('is_group')
                            ->label('Group conversation'),
                        TextInput::make('title')
                            ->maxLength(160)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
