<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DirectMessageService
{
    /**
     * @return array{allowed: bool, is_request: bool}
     */
    private function evaluateDmPolicy(User $sender, User $recipient): array
    {
        $policy = $recipient->dm_policy ?? User::DM_EVERYONE;

        if ($policy === User::DM_NONE) {
            return ['allowed' => false, 'is_request' => false];
        }

        $recipientFollowsSender = $recipient
            ->following()
            ->where('followed_id', $sender->id)
            ->exists();

        if ($recipientFollowsSender) {
            return ['allowed' => true, 'is_request' => false];
        }

        if (! ($recipient->dm_allow_requests ?? true)) {
            return ['allowed' => false, 'is_request' => false];
        }

        // Not followed: deliver as a message request.
        return ['allowed' => true, 'is_request' => true];
    }

    public function findOrCreate(User $a, User $b): Conversation
    {
        if ($a->is($b)) {
            throw new \InvalidArgumentException('Cannot create a direct conversation with yourself.');
        }

        if ($a->isBlockedEitherWay($b)) {
            throw new \RuntimeException('Direct messages are not available between these users.');
        }

        $policy = $this->evaluateDmPolicy(sender: $a, recipient: $b);
        if (! $policy['allowed']) {
            throw new \RuntimeException('This user is not accepting direct messages.');
        }

        $existing = Conversation::query()
            ->where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $a->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $b->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $isRequest = $policy['is_request'];

        return DB::transaction(function () use ($a, $b, $isRequest) {
            $conversation = Conversation::query()->create([
                'created_by_user_id' => $a->id,
                'is_group' => false,
                'title' => null,
            ]);

            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $a->id,
                'is_request' => false,
            ]);

            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $b->id,
                'is_request' => $isRequest,
            ]);

            return $conversation;
        });
    }
}
