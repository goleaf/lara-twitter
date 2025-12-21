<?php

namespace App\Filament\Resources\MessageReactions\Pages;

use App\Filament\Resources\MessageReactions\MessageReactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessageReactions extends ListRecords
{
    protected static string $resource = MessageReactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
