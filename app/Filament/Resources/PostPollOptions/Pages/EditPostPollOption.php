<?php

namespace App\Filament\Resources\PostPollOptions\Pages;

use App\Filament\Resources\PostPollOptions\PostPollOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostPollOption extends EditRecord
{
    protected static string $resource = PostPollOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
