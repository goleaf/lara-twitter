<?php

namespace App\Filament\Resources\Spaces\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SpaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
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
                        Select::make('pinned_post_id')
                            ->label('Pinned post')
                            ->relationship('pinnedPost', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->helperText('Optional post to highlight during the space.'),
                    ])
                    ->columns(2),
                Section::make('Scheduling')
                    ->schema([
                        DateTimePicker::make('scheduled_for'),
                        DateTimePicker::make('started_at'),
                        DateTimePicker::make('ended_at'),
                    ])
                    ->columns(2),
                Section::make('Recording')
                    ->schema([
                        Toggle::make('recording_enabled')
                            ->label('Recording enabled'),
                        DateTimePicker::make('recording_available_until')
                            ->label('Recording available until'),
                    ])
                    ->columns(2),
            ]);
    }
}
