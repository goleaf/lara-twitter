<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class SearchPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $slug = 'search';
    protected static ?string $title = 'Search';

    protected static string $view = 'filament-panels::pages.page';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Search (Filament page)'),
            Text::make('Next: advanced search form + results table.')->color('gray'),
        ]);
    }
}

