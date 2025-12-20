<?php

namespace App\Filament\Resources\SpaceSpeakerRequests\Pages;

use App\Filament\Resources\SpaceSpeakerRequests\SpaceSpeakerRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpaceSpeakerRequests extends ListRecords
{
    protected static string $resource = SpaceSpeakerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
