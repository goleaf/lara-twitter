<?php

namespace App\Filament\Resources\PostImages\Pages;

use App\Filament\Resources\PostImages\PostImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostImages extends ListRecords
{
    protected static string $resource = PostImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
