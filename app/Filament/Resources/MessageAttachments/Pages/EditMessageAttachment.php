<?php

namespace App\Filament\Resources\MessageAttachments\Pages;

use App\Filament\Resources\MessageAttachments\MessageAttachmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageAttachment extends EditRecord
{
    protected static string $resource = MessageAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
