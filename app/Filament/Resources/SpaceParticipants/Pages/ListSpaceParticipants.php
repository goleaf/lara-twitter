<?php

namespace App\Filament\Resources\SpaceParticipants\Pages;

use App\Filament\Resources\SpaceParticipants\SpaceParticipantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpaceParticipants extends ListRecords
{
    protected static string $resource = SpaceParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
