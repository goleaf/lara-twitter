<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\UserList;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProfileListsPage extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->loadCount(['followers', 'following']);

        if (Auth::check() && Auth::id() !== $this->user->id) {
            abort_if(Auth::user()->isBlockedEitherWay($this->user), 403);
        }
    }

    public function getListsProperty()
    {
        return UserList::query()
            ->where('is_private', false)
            ->whereHas('members', fn ($q) => $q->where('users.id', $this->user->id))
            ->with('owner')
            ->withCount(['members', 'subscribers'])
            ->latest()
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.profile-lists-page');
    }
}
