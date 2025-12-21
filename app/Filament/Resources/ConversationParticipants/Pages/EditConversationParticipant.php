<?php

namespace App\Filament\Resources\ConversationParticipants\Pages;

use App\Filament\Resources\ConversationParticipants\ConversationParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConversationParticipant extends EditRecord
{
    protected static string $resource = ConversationParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
