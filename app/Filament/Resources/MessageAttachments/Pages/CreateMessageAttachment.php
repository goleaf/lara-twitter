<?php

namespace App\Filament\Resources\MessageAttachments\Pages;

use App\Filament\Resources\MessageAttachments\MessageAttachmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessageAttachment extends CreateRecord
{
    protected static string $resource = MessageAttachmentResource::class;
}
