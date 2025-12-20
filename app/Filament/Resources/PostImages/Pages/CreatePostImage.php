<?php

namespace App\Filament\Resources\PostImages\Pages;

use App\Filament\Resources\PostImages\PostImageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostImage extends CreateRecord
{
    protected static string $resource = PostImageResource::class;
}
