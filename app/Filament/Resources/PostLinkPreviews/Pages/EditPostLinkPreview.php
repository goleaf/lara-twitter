<?php

namespace App\Filament\Resources\PostLinkPreviews\Pages;

use App\Filament\Resources\PostLinkPreviews\PostLinkPreviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostLinkPreview extends EditRecord
{
    protected static string $resource = PostLinkPreviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
