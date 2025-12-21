<?php

namespace App\Filament\Resources\ConversationParticipants\Pages;

use App\Filament\Resources\ConversationParticipants\ConversationParticipantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConversationParticipants extends ListRecords
{
    protected static string $resource = ConversationParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
