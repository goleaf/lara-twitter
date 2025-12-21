<?php

namespace App\Filament\Resources\ConversationParticipants\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConversationParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Participant')
                    ->schema([
                        Select::make('conversation_id')
                            ->relationship('conversation', 'id')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        DateTimePicker::make('last_read_at')
                            ->label('Last read')
                            ->seconds(false)
                            ->native(false),
                        Toggle::make('is_request')
                            ->label('Request'),
                        Toggle::make('is_pinned')
                            ->label('Pinned'),
                        TextInput::make('role')
                            ->maxLength(50)
                            ->default('member'),
                    ])
                    ->columns(2),
            ]);
    }
}
