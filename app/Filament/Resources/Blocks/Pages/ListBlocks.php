<?php

namespace App\Filament\Resources\Blocks\Pages;

use App\Filament\Resources\Blocks\BlockResource;
use App\Filament\Resources\Pages\CompositeKeyListRecords;
use Filament\Actions\CreateAction;

class ListBlocks extends CompositeKeyListRecords
{
    protected static string $resource = BlockResource::class;

    protected function getCompositeKeyColumns(): array
    {
        return ['blocker_id', 'blocked_id'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
