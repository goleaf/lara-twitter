<?php

namespace App\Filament\Resources\PostPollVotes\Pages;

use App\Filament\Resources\PostPollVotes\PostPollVoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostPollVote extends CreateRecord
{
    protected static string $resource = PostPollVoteResource::class;
}
