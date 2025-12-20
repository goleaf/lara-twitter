<?php

namespace App\Filament\Resources\MomentItems\Pages;

use App\Filament\Resources\MomentItems\MomentItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMomentItem extends EditRecord
{
    protected static string $resource = MomentItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
