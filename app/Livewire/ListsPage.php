<?php

namespace App\Livewire;

use App\Http\Requests\Lists\StoreListRequest;
use App\Models\UserList;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListsPage extends Component
{
    public string $name = '';

    public string $description = '';

    public bool $is_private = false;

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function create(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StoreListRequest::rulesFor());

        UserList::query()->create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'is_private' => (bool) ($validated['is_private'] ?? false),
        ]);

        $this->reset(['name', 'description', 'is_private']);
    }

    public function getOwnedListsProperty()
    {
        return Auth::user()
            ->listsOwned()
            ->withCount('members')
            ->latest()
            ->get();
    }

    public function getMemberListsProperty()
    {
        return Auth::user()
            ->listsMemberOf()
            ->where('user_lists.is_private', false)
            ->with('owner')
            ->withCount('members')
            ->latest()
            ->get();
    }

    public function getSubscribedListsProperty()
    {
        return Auth::user()
            ->listsSubscribed()
            ->with('owner')
            ->withCount('members')
            ->latest('user_list_subscriptions.created_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.lists-page')->layout('layouts.app');
    }
}
