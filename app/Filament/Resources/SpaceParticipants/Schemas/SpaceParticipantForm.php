<?php

namespace App\Filament\Resources\SpaceParticipants\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SpaceParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Participant')
                    ->schema([
                        Select::make('space_id')
                            ->relationship('space', 'title')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        Select::make('role')
                            ->options([
                                'host' => 'Host',
                                'speaker' => 'Speaker',
                                'listener' => 'Listener',
                            ])
                            ->required(),
                        DateTimePicker::make('joined_at')
                            ->label('Joined at'),
                        DateTimePicker::make('left_at')
                            ->label('Left at'),
                    ])
                    ->columns(2),
            ]);
    }
}
