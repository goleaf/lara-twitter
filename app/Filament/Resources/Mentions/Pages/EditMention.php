<?php

namespace App\Filament\Resources\Mentions\Pages;

use App\Filament\Resources\Mentions\MentionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMention extends EditRecord
{
    protected static string $resource = MentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
