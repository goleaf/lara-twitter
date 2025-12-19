<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class MessagesPage extends Component
{
    #[Url]
    public string $tab = 'inbox';

    #[Url]
    public string $q = '';

    public function updatedTab(): void
    {
        // no-op: kept for livewire URL updates
    }

    public function togglePin(int $conversationId): void
    {
        abort_unless(Auth::check(), 403);

        $participant = \App\Models\ConversationParticipant::query()
            ->where('conversation_id', $conversationId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $participant->update(['is_pinned' => ! $participant->is_pinned]);
    }

    private function normalizedTab(): string
    {
        return in_array($this->tab, ['inbox', 'requests'], true) ? $this->tab : 'inbox';
    }

    public function getConversationsProperty()
    {
        abort_unless(Auth::check(), 403);

        $isRequests = $this->normalizedTab() === 'requests';
        $search = trim($this->q);

        return Conversation::query()
            ->select('conversations.*')
            ->join('conversation_participants as cp', 'cp.conversation_id', '=', 'conversations.id')
            ->where('cp.user_id', Auth::id())
            ->where('cp.is_request', $isRequests)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('conversations.title', 'like', "%{$search}%")
                        ->orWhereHas('participants.user', function ($u) use ($search) {
                            $u->where('users.username', 'like', "%{$search}%")
                                ->orWhere('users.name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('messages', function ($m) use ($search) {
                            $m->where('body', 'like', "%{$search}%");
                        });
                });
            })
            ->with([
                'participants.user',
                'messages' => fn ($q) => $q->latest()->limit(1)->with('attachments'),
            ])
            ->orderByDesc('cp.is_pinned')
            ->orderByDesc('conversations.updated_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.messages-page')->layout('layouts.app');
    }
}
