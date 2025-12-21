<?php

namespace App\Filament\Resources\SpaceReactions\Pages;

use App\Filament\Resources\SpaceReactions\SpaceReactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpaceReactions extends ListRecords
{
    protected static string $resource = SpaceReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
