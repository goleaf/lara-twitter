<?php

namespace App\Filament\Resources\SpaceParticipants\Pages;

use App\Filament\Resources\SpaceParticipants\SpaceParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpaceParticipant extends EditRecord
{
    protected static string $resource = SpaceParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
