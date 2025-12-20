<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class BookmarksPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $slug = 'bookmarks';
    protected static ?string $title = 'Bookmarks';
    protected static ?int $navigationSort = 50;

    protected static string | array $routeMiddleware = ['auth'];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Bookmarks (Filament page)'),
            Text::make('Next: implement a Filament table for saved posts.')->color('gray'),
        ]);
    }
}
