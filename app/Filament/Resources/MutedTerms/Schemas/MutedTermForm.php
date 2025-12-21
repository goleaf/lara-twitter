<?php

namespace App\Filament\Resources\MutedTerms\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MutedTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Muted term')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        TextInput::make('term')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->columns(2),
                Section::make('Rules')
                    ->schema([
                        Toggle::make('whole_word')
                            ->label('Whole word'),
                        Toggle::make('only_non_followed')
                            ->label('Only non-followed'),
                        Toggle::make('mute_timeline')
                            ->label('Mute timeline'),
                        Toggle::make('mute_notifications')
                            ->label('Mute notifications'),
                        DateTimePicker::make('expires_at')
                            ->label('Expires at'),
                    ])
                    ->columns(2),
            ]);
    }
}
