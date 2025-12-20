<?php

namespace App\Filament\Resources\MutedTerms\Pages;

use App\Filament\Resources\MutedTerms\MutedTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMutedTerms extends ListRecords
{
    protected static string $resource = MutedTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
