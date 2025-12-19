<?php

namespace App\Livewire;

use App\Http\Requests\Messages\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceived;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ConversationPage extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Conversation $conversation;

    public ?string $body = '';

    /** @var array<int, mixed> */
    public array $attachments = [];

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

        $this->markRead();
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
            ->with(['user', 'attachments'])
            ->latest()
            ->paginate(30);
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
            $blockedEitherWay = \App\Models\Block::query()
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
        $others = \App\Models\User::query()->whereIn('id', $otherIds)->get()->keyBy('id');
        foreach ($others as $other) {
            if ($other->dm_policy === \App\Models\User::DM_NONE) {
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
        return view('livewire.conversation-page')->layout('layouts.app');
    }
}
