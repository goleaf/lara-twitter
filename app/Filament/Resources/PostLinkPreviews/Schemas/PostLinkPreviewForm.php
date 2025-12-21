<?php

namespace App\Filament\Resources\PostLinkPreviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostLinkPreviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Link preview')
                    ->schema([
                        Select::make('post_id')
                            ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->required(),
                        TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        TextInput::make('site_name')
                            ->label('Site name')
                            ->maxLength(100),
                        TextInput::make('title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        DateTimePicker::make('fetched_at')
                            ->label('Fetched at')
                            ->seconds(false)
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
