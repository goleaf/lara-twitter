<?php

namespace App\Filament\Resources\Bookmarks\Pages;

use App\Filament\Resources\Bookmarks\BookmarkResource;
use App\Filament\Resources\Pages\CompositeKeyListRecords;
use Filament\Actions\CreateAction;

class ListBookmarks extends CompositeKeyListRecords
{
    protected static string $resource = BookmarkResource::class;

    protected function getCompositeKeyColumns(): array
    {
        return ['user_id', 'post_id'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
