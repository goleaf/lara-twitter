<?php

namespace App\Filament\Resources\Follows\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class FollowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Follow')
                    ->schema([
                        Select::make('follower_id')
                            ->relationship('follower', 'username')
                            ->searchable()
                            ->required(),
                        Select::make('followed_id')
                            ->relationship('followed', 'username')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
