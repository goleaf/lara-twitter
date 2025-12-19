<?php

namespace App\Livewire;

use App\Http\Requests\Messages\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
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

    public function mount(Conversation $conversation): void
    {
        abort_unless(Auth::check(), 403);

        $conversation->load(['participants.user']);

        abort_unless($conversation->hasParticipant(Auth::user()), 403);

        $this->conversation = $conversation;

        $this->markRead();
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
