<?php

namespace App\Livewire;

use App\Http\Requests\Messages\CreateConversationRequest;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Services\DirectMessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class NewConversationPage extends Component
{
    public string $title = '';

    public string $recipientUsername = '';

    /** @var array<int, int> */
    public array $recipientUserIds = [];

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function getRecipientsProperty()
    {
        return User::query()
            ->select(['id', 'name', 'username', 'avatar_path', 'is_verified'])
            ->whereIn('id', $this->recipientUserIds)
            ->orderBy('username')
            ->get();
    }

    public function addRecipient(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate([
            'recipientUsername' => ['required', 'string', 'max:50'],
        ]);

        $username = mb_strtolower(ltrim(trim($validated['recipientUsername']), '@'));

        $user = User::query()->where('username', $username)->first();
        if (! $user) {
            $this->addError('recipientUsername', 'User not found.');

            return;
        }

        if ($user->id === Auth::id()) {
            $this->addError('recipientUsername', 'You cannot message yourself.');

            return;
        }

        if (in_array($user->id, $this->recipientUserIds, true)) {
            $this->addError('recipientUsername', 'Already added.');

            return;
        }

        if (count($this->recipientUserIds) >= 49) {
            $this->addError('recipientUsername', 'Group chats are limited to 50 people.');

            return;
        }

        if (Auth::user()->isBlockedEitherWay($user)) {
            $this->addError('recipientUsername', 'Direct messages are not available between these users.');

            return;
        }

        $policy = app(DirectMessageService::class)->policyFor(sender: Auth::user(), recipient: $user);
        if (! $policy['allowed']) {
            $this->addError('recipientUsername', 'This user is not accepting direct messages.');

            return;
        }

        $this->recipientUserIds[] = $user->id;
        $this->recipientUsername = '';
    }

    public function removeRecipient(int $userId): void
    {
        abort_unless(Auth::check(), 403);

        $this->recipientUserIds = array_values(array_filter(
            $this->recipientUserIds,
            fn (int $id) => $id !== $userId,
        ));
    }

    public function create(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(CreateConversationRequest::rulesFor());

        $recipientIds = array_values(array_unique(array_map('intval', $validated['recipientUserIds'])));

        if (count($recipientIds) === 1) {
            $recipient = User::query()->findOrFail($recipientIds[0]);

            try {
                $conversation = app(DirectMessageService::class)->findOrCreate(Auth::user(), $recipient);
            } catch (\Throwable) {
                abort(403);
            }

            $this->redirect(route('messages.show', $conversation), navigate: true);

            return;
        }

        $recipients = User::query()
            ->whereIn('id', $recipientIds)
            ->get()
            ->keyBy('id');

        foreach ($recipientIds as $id) {
            $recipient = $recipients->get($id);
            abort_if(! $recipient, 422);

            abort_if(Auth::user()->isBlockedEitherWay($recipient), 403);

            $policy = app(DirectMessageService::class)->policyFor(sender: Auth::user(), recipient: $recipient);
            abort_if(! $policy['allowed'], 403);
        }

        $participantIds = array_merge([Auth::id()], $recipientIds);
        $blocked = Block::query()
            ->whereIn('blocker_id', $participantIds)
            ->whereIn('blocked_id', $participantIds)
            ->exists();

        abort_if($blocked, 403);

        $title = trim((string) ($validated['title'] ?? ''));

        $conversation = DB::transaction(function () use ($recipientIds, $recipients, $title) {
            $conversation = Conversation::query()->create([
                'created_by_user_id' => Auth::id(),
                'is_group' => true,
                'title' => $title !== '' ? $title : null,
            ]);

            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'last_read_at' => now(),
                'is_request' => false,
                'role' => 'admin',
            ]);

            foreach ($recipientIds as $recipientId) {
                $recipient = $recipients->get($recipientId);
                abort_if(! $recipient, 422);

                $policy = app(DirectMessageService::class)->policyFor(sender: Auth::user(), recipient: $recipient);
                abort_if(! $policy['allowed'], 403);

                ConversationParticipant::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $recipientId,
                    'is_request' => $policy['is_request'],
                    'role' => 'member',
                ]);
            }

            return $conversation;
        });

        $this->redirect(route('messages.show', $conversation), navigate: true);
    }

    public function render()
    {
        return view('livewire.new-conversation-page')->layout('layouts.app');
    }
}
