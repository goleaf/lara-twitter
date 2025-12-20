<?php

namespace App\Filament\Resources\PostPolls\Pages;

use App\Filament\Resources\PostPolls\PostPollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostPolls extends ListRecords
{
    protected static string $resource = PostPollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
