<?php

namespace App\Filament\Resources\MomentItems\Pages;

use App\Filament\Resources\MomentItems\MomentItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMomentItems extends ListRecords
{
    protected static string $resource = MomentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
