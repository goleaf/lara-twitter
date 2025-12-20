<?php

namespace App\Filament\Resources\PostImages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Image')
                    ->schema([
                        Select::make('post_id')
                            ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->required(),
                        FileUpload::make('path')
                            ->label('Image')
                            ->disk('public')
                            ->directory('posts')
                            ->image()
                            ->required(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
