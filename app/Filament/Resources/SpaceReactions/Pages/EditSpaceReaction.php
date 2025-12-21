<?php

namespace App\Filament\Resources\SpaceReactions\Pages;

use App\Filament\Resources\SpaceReactions\SpaceReactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpaceReaction extends EditRecord
{
    protected static string $resource = SpaceReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
