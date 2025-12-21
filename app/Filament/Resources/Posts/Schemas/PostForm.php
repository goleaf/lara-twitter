<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->required(),
                        Textarea::make('body')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('location')
                            ->maxLength(120)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Reply & Repost')
                    ->schema([
                        Select::make('reply_to_id')
                            ->relationship('replyTo', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable(),
                        Select::make('repost_of_id')
                            ->relationship('repostOf', 'id', fn ($query) => $query->withoutGlobalScope('published'))
                            ->searchable(),
                        Select::make('reply_policy')
                            ->options(array_combine(Post::replyPolicies(), Post::replyPolicies()))
                            ->default(Post::REPLY_EVERYONE)
                            ->required(),
                        Toggle::make('is_reply_like')
                            ->label('Reply-like'),
                    ])
                    ->columns(2),
                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published'),
                        DateTimePicker::make('scheduled_for')
                            ->label('Scheduled for')
                            ->seconds(false)
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
