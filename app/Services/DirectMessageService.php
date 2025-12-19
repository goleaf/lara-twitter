<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DirectMessageService
{
    public function findOrCreate(User $a, User $b): Conversation
    {
        if ($a->is($b)) {
            throw new \InvalidArgumentException('Cannot create a direct conversation with yourself.');
        }

        if ($a->isBlockedEitherWay($b)) {
            throw new \RuntimeException('Direct messages are not available between these users.');
        }

        $existing = Conversation::query()
            ->where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $a->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $b->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($a, $b) {
            $conversation = Conversation::query()->create([
                'created_by_user_id' => $a->id,
                'is_group' => false,
                'title' => null,
            ]);

            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $a->id,
            ]);

            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $b->id,
            ]);

            return $conversation;
        });
    }
}
