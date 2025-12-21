<?php

namespace App\Filament\Resources\Follows\Pages;

use App\Filament\Resources\Follows\FollowResource;
use App\Filament\Resources\Pages\CompositeKeyListRecords;
use Filament\Actions\CreateAction;

class ListFollows extends CompositeKeyListRecords
{
    protected static string $resource = FollowResource::class;

    protected function getCompositeKeyColumns(): array
    {
        return ['follower_id', 'followed_id'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
