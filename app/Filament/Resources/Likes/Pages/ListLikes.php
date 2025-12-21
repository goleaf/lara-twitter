<?php

namespace App\Filament\Resources\Likes\Pages;

use App\Filament\Resources\Likes\LikeResource;
use App\Filament\Resources\Pages\CompositeKeyListRecords;
use Filament\Actions\CreateAction;

class ListLikes extends CompositeKeyListRecords
{
    protected static string $resource = LikeResource::class;

    protected function getCompositeKeyColumns(): array
    {
        return ['user_id', 'post_id'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
