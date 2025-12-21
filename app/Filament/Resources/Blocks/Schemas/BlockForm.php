<?php

namespace App\Filament\Resources\Blocks\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class BlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Block')
                    ->schema([
                        Select::make('blocker_id')
                            ->relationship('blocker', 'username')
                            ->searchable()
                            ->required(),
                        Select::make('blocked_id')
                            ->relationship('blocked', 'username')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
