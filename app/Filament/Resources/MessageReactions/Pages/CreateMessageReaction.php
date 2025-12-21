<?php

namespace App\Filament\Resources\MessageReactions\Pages;

use App\Filament\Resources\MessageReactions\MessageReactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessageReaction extends CreateRecord
{
    protected static string $resource = MessageReactionResource::class;
}
