<?php

namespace App\Filament\Resources\Mentions\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MentionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mention')
                    ->schema([
                        Select::make('post_id')
                            ->relationship('post', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable()
                            ->required(),
                        Select::make('mentioned_user_id')
                            ->relationship('mentionedUser', 'username')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
