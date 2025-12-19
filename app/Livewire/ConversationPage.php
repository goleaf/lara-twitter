<?php

namespace App\Livewire;

use App\Http\Requests\Messages\AddConversationMemberRequest;
use App\Http\Requests\Messages\StoreMessageRequest;
use App\Http\Requests\Messages\UpdateConversationTitleRequest;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceived;
use App\Services\DirectMessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ConversationPage extends Component
{
    use WithFileUploads;
    use WithPagination;

    private const TYPING_TTL_SECONDS = 6;
    private const UNSEND_WINDOW_MINUTES = 5;

    public Conversation $conversation;

    public ?string $body = '';

    /** @var array<int, mixed> */
    public array $attachments = [];

    public string $groupTitle = '';

    public string $memberUsername = '';

    public function getMyParticipantProperty(): ?ConversationParticipant
    {
        if (! Auth::check()) {
            return null;
        }

        return $this->conversation
            ->participants()
            ->where('user_id', Auth::id())
            ->first();
    }

    public function mount(Conversation $conversation): void
    {
        abort_unless(Auth::check(), 403);

        $conversation->load(['participants.user']);

        abort_unless($conversation->hasParticipant(Auth::user()), 403);

        $this->conversation = $conversation;
        $this->groupTitle = (string) ($conversation->title ?? '');

        $this->markRead();
    }

    public function getIsGroupAdminProperty(): bool
    {
        return (bool) ($this->conversation->is_group && $this->myParticipant?->role === 'admin');
    }

    public function acceptRequest(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        $this->conversation
            ->participants()
            ->where('user_id', Auth::id())
            ->update(['is_request' => false]);
    }

    public function declineRequest(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        $this->conversation
            ->participants()
            ->where('user_id', Auth::id())
            ->delete();

        if (! $this->conversation->is_group && $this->conversation->participants()->count() < 2) {
            $this->conversation->delete();
        } elseif ($this->conversation->participants()->count() === 0) {
            $this->conversation->delete();
        }

        $this->redirect(route('messages.index'), navigate: true);
    }

    public function getMessagesProperty()
    {
        return Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->with(['user', 'attachments', 'reactions'])
            ->latest()
            ->paginate(30);
    }

    private function typingCacheKey(int $userId): string
    {
        return "dm:typing:{$this->conversation->id}:{$userId}";
    }

    public function typing(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        if ($this->myParticipant?->is_request) {
            return;
        }

        Cache::put(
            $this->typingCacheKey(Auth::id()),
            now()->timestamp,
            now()->addSeconds(self::TYPING_TTL_SECONDS),
        );
    }

    public function getTypingUsernamesProperty()
    {
        if (! Auth::check()) {
            return collect();
        }

        $participants = $this->conversation
            ->participants()
            ->with('user')
            ->where('user_id', '!=', Auth::id())
            ->get();

        return $participants
            ->filter(fn (ConversationParticipant $p) => Cache::has($this->typingCacheKey($p->user_id)))
            ->map(fn (ConversationParticipant $p) => $p->user?->username)
            ->filter()
            ->values();
    }

    /**
     * @return array<int, string>
     */
    public function reactionEmojis(): array
    {
        return ['👍', '❤️', '😂', '😮', '😢', '👎'];
    }

    public function toggleReaction(int $messageId, string $emoji): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        if ($this->myParticipant?->is_request) {
            abort(403);
        }

        abort_unless(in_array($emoji, $this->reactionEmojis(), true), 422);

        $message = Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->where('id', $messageId)
            ->firstOrFail();

        $existing = $message->reactions()
            ->where('user_id', Auth::id())
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $message->reactions()->create([
                'user_id' => Auth::id(),
                'emoji' => $emoji,
            ]);
        }

        $this->dispatch('$refresh');
    }

    public function canUnsend(Message $message): bool
    {
        if (! Auth::check()) {
            return false;
        }

        if ($message->user_id !== Auth::id()) {
            return false;
        }

        if (! $message->created_at) {
            return false;
        }

        return $message->created_at->greaterThanOrEqualTo(now()->subMinutes(self::UNSEND_WINDOW_MINUTES));
    }

    public function unsend(int $messageId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        $message = Message::query()
            ->where('conversation_id', $this->conversation->id)
            ->where('id', $messageId)
            ->with(['attachments'])
            ->firstOrFail();

        abort_unless($this->canUnsend($message), 403);

        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $message->attachments()->delete();
        $message->reactions()->delete();
        $message->delete();

        $this->conversation->touch();

        $this->resetPage();
        $this->dispatch('$refresh');
    }

    public function readReceiptLabelFor(Message $message): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        if (! (Auth::user()->dm_read_receipts ?? true)) {
            return null;
        }

        if ($message->user_id !== Auth::id()) {
            return null;
        }

        $participants = $this->conversation
            ->participants()
            ->with('user')
            ->where('user_id', '!=', Auth::id())
            ->get()
            ->filter(fn (ConversationParticipant $p) => ! $p->is_request)
            ->filter(fn (ConversationParticipant $p) => (bool) ($p->user?->dm_read_receipts ?? true));

        if ($participants->isEmpty()) {
            return null;
        }

        $allRead = $participants->every(function (ConversationParticipant $p) use ($message) {
            return $p->last_read_at && $p->last_read_at->greaterThanOrEqualTo($message->created_at);
        });

        return $allRead ? 'Seen' : null;
    }

    public function updateGroupTitle(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);
        abort_unless($this->conversation->is_group, 403);
        abort_unless($this->isGroupAdmin, 403);

        $validated = $this->validate(UpdateConversationTitleRequest::rulesFor());
        $title = trim((string) ($validated['groupTitle'] ?? ''));

        $this->conversation->update(['title' => $title !== '' ? $title : null]);
        $this->conversation->refresh();
    }

    public function addMember(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);
        abort_unless($this->conversation->is_group, 403);
        abort_unless($this->isGroupAdmin, 403);

        $validated = $this->validate(AddConversationMemberRequest::rulesFor());
        $username = mb_strtolower(ltrim(trim($validated['memberUsername']), '@'));

        $user = User::query()->where('username', $username)->first();
        if (! $user) {
            $this->addError('memberUsername', 'User not found.');

            return;
        }

        if ($this->conversation->hasParticipant($user)) {
            $this->addError('memberUsername', 'User is already in this group.');

            return;
        }

        $participantIds = $this->conversation->participants()->pluck('user_id')->all();
        if (count($participantIds) >= 50) {
            $this->addError('memberUsername', 'Group chats are limited to 50 people.');

            return;
        }

        $blocked = Block::query()
            ->where(function ($q) use ($participantIds, $user) {
                $q->where('blocker_id', $user->id)->whereIn('blocked_id', $participantIds);
            })
            ->orWhere(function ($q) use ($participantIds, $user) {
                $q->whereIn('blocker_id', $participantIds)->where('blocked_id', $user->id);
            })
            ->exists();

        if ($blocked) {
            $this->addError('memberUsername', 'Direct messages are not available between these users.');

            return;
        }

        $policy = app(DirectMessageService::class)->policyFor(sender: Auth::user(), recipient: $user);
        if (! $policy['allowed']) {
            $this->addError('memberUsername', 'This user is not accepting direct messages.');

            return;
        }

        ConversationParticipant::query()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $user->id,
            'is_request' => $policy['is_request'],
            'role' => 'member',
        ]);

        $this->reset('memberUsername');
        $this->conversation->refresh()->load(['participants.user']);
    }

    public function removeMember(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);
        abort_unless($this->conversation->is_group, 403);
        abort_unless($this->isGroupAdmin, 403);

        if ($userId === Auth::id()) {
            abort(403);
        }

        $this->conversation->participants()->where('user_id', $userId)->delete();

        $remaining = $this->conversation->participants()->count();
        if ($remaining < 2) {
            $this->conversation->delete();
            $this->redirect(route('messages.index'), navigate: true);

            return;
        }

        $this->conversation->refresh()->load(['participants.user']);
    }

    public function leaveGroup(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);
        abort_unless($this->conversation->is_group, 403);

        $my = $this->myParticipant;
        abort_if(! $my, 403);

        if ($my->role === 'admin') {
            $replacement = $this->conversation
                ->participants()
                ->where('user_id', '!=', Auth::id())
                ->orderBy('id')
                ->first();

            if ($replacement) {
                $replacement->update(['role' => 'admin']);
            }
        }

        $this->conversation->participants()->where('user_id', Auth::id())->delete();

        $remaining = $this->conversation->participants()->count();
        if ($remaining < 2) {
            $this->conversation->delete();
        }

        $this->redirect(route('messages.index'), navigate: true);
    }

    public function send(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->conversation->hasParticipant(Auth::user()), 403);

        if ($this->myParticipant?->is_request) {
            abort(403);
        }

        $otherIds = $this->conversation
            ->participants()
            ->where('user_id', '!=', Auth::id())
            ->pluck('user_id')
            ->all();

        if (! empty($otherIds)) {
            $blockedEitherWay = Block::query()
                ->where(function ($q) use ($otherIds) {
                    $q->where('blocker_id', Auth::id())->whereIn('blocked_id', $otherIds);
                })
                ->orWhere(function ($q) use ($otherIds) {
                    $q->whereIn('blocker_id', $otherIds)->where('blocked_id', Auth::id());
                })
                ->exists();

            abort_if($blockedEitherWay, 403);
        }

        // Enforce DM privacy settings for other participants. Existing conversations are allowed
        // unless the recipient has set DM policy to "none".
        $others = User::query()->whereIn('id', $otherIds)->get()->keyBy('id');
        foreach ($others as $other) {
            if ($other->dm_policy === User::DM_NONE) {
                abort(403);
            }
        }

        $validated = $this->validate(StoreMessageRequest::rulesFor());

        $hasBody = isset($validated['body']) && trim((string) $validated['body']) !== '';
        $hasFiles = ! empty($validated['attachments']);

        if (! $hasBody && ! $hasFiles) {
            $this->addError('body', 'Message text or an attachment is required.');

            return;
        }

        $message = Message::query()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => Auth::id(),
            'body' => $hasBody ? $validated['body'] : null,
        ]);

        foreach ($validated['attachments'] as $index => $file) {
            $path = $file->storePublicly("messages/{$this->conversation->id}/{$message->id}", ['disk' => 'public']);

            $message->attachments()->create([
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'sort_order' => $index,
            ]);
        }

        $this->reset(['body', 'attachments']);
        $this->resetPage();

        $this->conversation->touch();

        $recipients = User::query()
            ->whereIn('id', $otherIds)
            ->get()
            ->filter(fn (User $u) => ! $u->isBlockedEitherWay(Auth::user()));

        foreach ($recipients as $recipient) {
            $participant = $this->conversation->participantFor($recipient);
            if ($participant?->is_request) {
                continue;
            }

            if (! $recipient->wantsNotification('dms')) {
                continue;
            }

            if (! $recipient->allowsNotificationFrom(Auth::user(), 'dms')) {
                continue;
            }

            $recipient->notify(new MessageReceived(
                conversation: $this->conversation,
                message: $message,
                sender: Auth::user(),
            ));
        }

        $this->markRead();
    }

    public function markRead(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->conversation
            ->participants()
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);
    }

    public function render()
    {
        $this->conversation->loadMissing(['participants.user']);

        return view('livewire.conversation-page')->layout('layouts.app');
    }
}
