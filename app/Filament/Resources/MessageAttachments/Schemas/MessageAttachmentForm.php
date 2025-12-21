<?php

namespace App\Filament\Resources\MessageAttachments\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MessageAttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attachment')
                    ->schema([
                        Select::make('message_id')
                            ->relationship('message', 'id')
                            ->searchable()
                            ->required(),
                        TextInput::make('path')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('mime_type')
                            ->label('MIME type')
                            ->maxLength(100)
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
