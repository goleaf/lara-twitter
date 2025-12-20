<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class MessagesPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $slug = 'messages';
    protected static ?string $title = 'Messages';
    protected static ?int $navigationSort = 40;

    protected static string | array $routeMiddleware = ['auth'];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Messages (Filament page)'),
            Text::make('Next: DM inbox and conversation UI using Filament tables + forms.')->color('gray'),
        ]);
    }
}
