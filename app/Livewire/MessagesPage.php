<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MessagesPage extends Component
{
    public function getConversationsProperty()
    {
        abort_unless(Auth::check(), 403);

        return Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', Auth::id()))
            ->with([
                'participants.user',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest('updated_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.messages-page')->layout('layouts.app');
    }
}

