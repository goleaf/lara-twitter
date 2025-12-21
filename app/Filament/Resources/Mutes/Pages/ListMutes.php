<?php

namespace App\Filament\Resources\Mutes\Pages;

use App\Filament\Resources\Mutes\MuteResource;
use App\Filament\Resources\Pages\CompositeKeyListRecords;
use Filament\Actions\CreateAction;

class ListMutes extends CompositeKeyListRecords
{
    protected static string $resource = MuteResource::class;

    protected function getCompositeKeyColumns(): array
    {
        return ['muter_id', 'muted_id'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
