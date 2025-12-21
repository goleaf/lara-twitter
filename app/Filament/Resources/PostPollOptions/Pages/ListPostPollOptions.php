<?php

namespace App\Filament\Resources\PostPollOptions\Pages;

use App\Filament\Resources\PostPollOptions\PostPollOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostPollOptions extends ListRecords
{
    protected static string $resource = PostPollOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
