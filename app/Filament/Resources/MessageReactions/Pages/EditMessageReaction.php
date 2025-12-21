<?php

namespace App\Filament\Resources\MessageReactions\Pages;

use App\Filament\Resources\MessageReactions\MessageReactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageReaction extends EditRecord
{
    protected static string $resource = MessageReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
