<?php

namespace App\Filament\Resources\SpaceSpeakerRequests\Schemas;

use App\Models\SpaceSpeakerRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SpaceSpeakerRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request')
                    ->schema([
                        Select::make('space_id')
                            ->relationship('space', 'title')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->options(array_combine(SpaceSpeakerRequest::statuses(), SpaceSpeakerRequest::statuses()))
                            ->required(),
                        Select::make('decided_by')
                            ->relationship('decidedBy', 'username')
                            ->searchable(),
                        DateTimePicker::make('decided_at')
                            ->label('Decided at'),
                    ])
                    ->columns(2),
            ]);
    }
}
