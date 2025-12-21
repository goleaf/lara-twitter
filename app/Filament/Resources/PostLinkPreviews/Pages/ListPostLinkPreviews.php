<?php

namespace App\Filament\Resources\PostLinkPreviews\Pages;

use App\Filament\Resources\PostLinkPreviews\PostLinkPreviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostLinkPreviews extends ListRecords
{
    protected static string $resource = PostLinkPreviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
