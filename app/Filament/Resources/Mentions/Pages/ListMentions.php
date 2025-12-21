<?php

namespace App\Filament\Resources\Mentions\Pages;

use App\Filament\Resources\Mentions\MentionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMentions extends ListRecords
{
    protected static string $resource = MentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
