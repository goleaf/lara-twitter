<?php

namespace App\Livewire;

use App\Http\Requests\Lists\AddListMemberRequest;
use App\Models\Post;
use App\Models\User;
use App\Models\UserList;
use App\Notifications\AddedToList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListPage extends Component
{
    use WithPagination;

    public UserList $list;

    public string $member_username = '';

    public function mount(UserList $list): void
    {
        $list->load(['owner'])->loadCount('members', 'subscribers');

        abort_unless($list->isVisibleTo(Auth::user()), 403);

        $this->list = $list;
    }

    public function toggleSubscribe(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(! $this->list->is_private, 403);
        abort_unless(Auth::id() !== $this->list->owner_id, 403);

        $existing = $this->list->subscribers()->where('user_id', Auth::id())->exists();

        if ($existing) {
            $this->list->subscribers()->detach(Auth::id());
        } else {
            $this->list->subscribers()->attach(Auth::id());
        }

        $this->list->loadCount('subscribers');
    }

    public function isSubscribed(): bool
    {
        if (! Auth::check() || $this->list->is_private || Auth::id() === $this->list->owner_id) {
            return false;
        }

        return $this->list->subscribers()->where('user_id', Auth::id())->exists();
    }

    public function addMember(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->list->owner_id, 403);

        $validated = $this->validate(AddListMemberRequest::rulesFor());
        $username = mb_strtolower(ltrim($validated['member_username'], '@'));

        $user = User::query()->where('username', $username)->first();
        if (! $user) {
            $this->addError('member_username', 'User not found.');
            return;
        }

        if ($user->id === $this->list->owner_id) {
            $this->addError('member_username', 'You are already the list owner.');
            return;
        }

        $changes = $this->list->members()->syncWithoutDetaching([$user->id]);

        if (! $this->list->is_private && in_array($user->id, $changes['attached'] ?? [], true)) {
            if ($user->wantsNotification('lists') && $user->allowsNotificationFrom(Auth::user())) {
                $user->notify(new AddedToList(list: $this->list->loadMissing('owner'), addedBy: Auth::user()));
            }
        }

        $this->reset('member_username');
        $this->list->loadCount('members');
        $this->dispatch('$refresh');
    }

    public function removeMember(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->list->owner_id, 403);

        $this->list->members()->detach($userId);
        $this->list->loadCount('members');
        $this->dispatch('$refresh');
    }

    public function getMembersProperty()
    {
        return $this->list->members()->orderBy('username')->get();
    }

    public function getPostsProperty()
    {
        $memberIds = $this->list->members()->pluck('users.id')->all();

        $query = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->whereIn('user_id', $memberIds)
            ->with([
                'user',
                'images',
                'repostOf' => fn ($q) => $q->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']),
            ])
            ->withCount(['likes', 'reposts', 'replies'])
            ->latest();

        if (Auth::check()) {
            $exclude = Auth::user()->excludedUserIds();
            if ($exclude->isNotEmpty()) {
                $query->whereNotIn('user_id', $exclude);
            }
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.list-page')->layout('layouts.app');
    }
}
