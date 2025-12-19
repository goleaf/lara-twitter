<?php

namespace App\Filament\Resources\Moments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MomentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->relationship('owner', 'username')
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(120),
                Textarea::make('description')
                    ->maxLength(280)
                    ->rows(3)
                    ->columnSpanFull(),
                FileUpload::make('cover_image_path')
                    ->disk('public')
                    ->directory('moments/covers')
                    ->image(),
                Toggle::make('is_public')
                    ->label('Public'),
            ]);
    }
}

