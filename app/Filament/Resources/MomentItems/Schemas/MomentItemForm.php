<?php

namespace App\Filament\Resources\MomentItems\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MomentItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Moment item')
                    ->schema([
                        Select::make('moment_id')
                            ->relationship('moment', 'title')
                            ->searchable()
                            ->required(),
                        Select::make('post_id')
                            ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->required(),
                        TextInput::make('caption')
                            ->maxLength(280)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
