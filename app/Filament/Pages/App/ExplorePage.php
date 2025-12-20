<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class ExplorePage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-compass';
    protected static ?string $slug = 'explore';
    protected static ?string $title = 'Explore';
    protected static ?int $navigationSort = 10;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Explore (Filament page)'),
            Text::make('Next: trending, search, and discovery widgets.')->color('gray'),
        ]);
    }
}
