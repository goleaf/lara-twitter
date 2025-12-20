<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class ProfilePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $slug = 'profile';
    protected static ?string $title = 'Profile';
    protected static ?int $navigationSort = 60;

    protected static string $view = 'filament-panels::pages.page';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Profile (Filament page)'),
            Text::make('Next: dynamic @username routes and profile infolists.')->color('gray'),
        ]);
    }
}
