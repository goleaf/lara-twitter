<?php

namespace App\Filament\Resources\Mutes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MuteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mute')
                    ->schema([
                        Select::make('muter_id')
                            ->relationship('muter', 'username')
                            ->searchable()
                            ->required(),
                        Select::make('muted_id')
                            ->relationship('muted', 'username')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
