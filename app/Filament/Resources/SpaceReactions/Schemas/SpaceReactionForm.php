<?php

namespace App\Filament\Resources\SpaceReactions\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SpaceReactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reaction')
                    ->schema([
                        Select::make('space_id')
                            ->relationship('space', 'id')
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
