<?php

namespace App\Filament\Resources\UserLists\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('List')
                    ->schema([
                        Select::make('owner_id')
                            ->relationship('owner', 'username')
                            ->searchable()
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(280)
                            ->columnSpanFull(),
                        Toggle::make('is_private')
                            ->label('Private'),
                    ])
                    ->columns(2),
            ]);
    }
}
