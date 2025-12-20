<?php

namespace App\Filament\Resources\MutedTerms\Pages;

use App\Filament\Resources\MutedTerms\MutedTermResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMutedTerm extends EditRecord
{
    protected static string $resource = MutedTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
