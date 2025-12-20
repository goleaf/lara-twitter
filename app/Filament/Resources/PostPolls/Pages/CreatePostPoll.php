<?php

namespace App\Filament\Resources\PostPolls\Pages;

use App\Filament\Resources\PostPolls\PostPollResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostPoll extends CreateRecord
{
    protected static string $resource = PostPollResource::class;
}
