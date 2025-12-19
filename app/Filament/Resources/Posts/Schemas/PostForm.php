<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('reply_to_id')
                    ->relationship('replyTo', 'id'),
                Select::make('reply_policy')
                    ->options(array_combine(Post::replyPolicies(), Post::replyPolicies()))
                    ->default(Post::REPLY_EVERYONE)
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
