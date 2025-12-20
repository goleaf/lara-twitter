<?php

namespace App\Filament\Resources\PostPolls\Pages;

use App\Filament\Resources\PostPolls\PostPollResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostPoll extends EditRecord
{
    protected static string $resource = PostPollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
