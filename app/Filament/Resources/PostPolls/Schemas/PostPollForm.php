<?php

namespace App\Filament\Resources\PostPolls\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PostPollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Poll')
                    ->schema([
                        Select::make('post_id')
                            ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
