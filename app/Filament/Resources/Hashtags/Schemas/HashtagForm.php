<?php

namespace App\Filament\Resources\Hashtags\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HashtagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hashtag')
                    ->schema([
                        TextInput::make('tag')
                            ->label('Tag')
                            ->required()
                            ->maxLength(80)
                            ->prefix('#'),
                    ]),
            ]);
    }
}
