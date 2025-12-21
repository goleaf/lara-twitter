<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\DirectMessageService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class NewConversationRedirect extends Component
{
    public function mount(User $user, DirectMessageService $service): void
    {
        abort_unless(auth()->check(), 403);

        try {
            $conversation = $service->findOrCreate(auth()->user(), $user);
        } catch (\Throwable) {
            abort(403);
        }

        $this->redirect(route('messages.show', $conversation), navigate: true);
    }

    public function render()
    {
        return view('livewire.new-conversation-redirect');
    }
}
