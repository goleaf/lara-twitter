<?php

namespace App\Filament\Pages\App;

use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

class NotificationsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $slug = 'notifications';
    protected static ?string $title = 'Notifications';
    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament-panels::pages.page';

    protected static string | array $routeMiddleware = ['auth'];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Notifications (Filament page)'),
            Text::make('Next: a Filament table for notifications, with filters and bulk actions.')->color('gray'),
        ]);
    }
}
