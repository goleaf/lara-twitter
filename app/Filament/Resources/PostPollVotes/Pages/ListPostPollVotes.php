<?php

namespace App\Filament\Resources\PostPollVotes\Pages;

use App\Filament\Resources\PostPollVotes\PostPollVoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostPollVotes extends ListRecords
{
    protected static string $resource = PostPollVoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
