<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class SettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $slug = 'settings';
    protected static ?string $title = 'Settings';
    protected static ?int $navigationSort = 70;

    protected static string $view = 'filament-panels::pages.page';

    protected static string | array $routeMiddleware = ['auth'];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Settings (Filament page)'),
            Text::make('Next: account settings form and privacy toggles.')->color('gray'),
        ]);
    }
}
