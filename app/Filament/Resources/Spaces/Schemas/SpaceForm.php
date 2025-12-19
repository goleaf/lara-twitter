<?php

namespace App\Filament\Resources\Spaces\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SpaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('host_user_id')
                    ->relationship('host', 'username')
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(120),
                Textarea::make('description')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                DateTimePicker::make('scheduled_for'),
                Select::make('recording_enabled')
                    ->options([0 => 'No', 1 => 'Yes'])
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),
                DateTimePicker::make('recording_available_until'),
            ]);
    }
}

