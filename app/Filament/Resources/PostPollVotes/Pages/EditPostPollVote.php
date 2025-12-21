<?php

namespace App\Filament\Resources\PostPollVotes\Pages;

use App\Filament\Resources\PostPollVotes\PostPollVoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostPollVote extends EditRecord
{
    protected static string $resource = PostPollVoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
