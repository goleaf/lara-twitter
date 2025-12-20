<?php

namespace App\Filament\Resources\SpaceSpeakerRequests\Pages;

use App\Filament\Resources\SpaceSpeakerRequests\SpaceSpeakerRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpaceSpeakerRequest extends EditRecord
{
    protected static string $resource = SpaceSpeakerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
