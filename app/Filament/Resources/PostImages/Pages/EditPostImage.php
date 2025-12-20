<?php

namespace App\Filament\Resources\PostImages\Pages;

use App\Filament\Resources\PostImages\PostImageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostImage extends EditRecord
{
    protected static string $resource = PostImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
