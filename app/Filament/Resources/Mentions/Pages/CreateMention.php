<?php

namespace App\Filament\Resources\Mentions\Pages;

use App\Filament\Resources\Mentions\MentionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMention extends CreateRecord
{
    protected static string $resource = MentionResource::class;
}
