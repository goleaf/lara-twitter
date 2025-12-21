<?php

namespace App\Filament\Resources\MessageAttachments\Pages;

use App\Filament\Resources\MessageAttachments\MessageAttachmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessageAttachments extends ListRecords
{
    protected static string $resource = MessageAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
