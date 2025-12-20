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

    private const MEMBER_PREVIEW_LIMIT = 12;

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

        $alreadyMember = $this->list->members()
            ->where('user_list_user.user_id', $user->id)
            ->exists();
        if ($alreadyMember) {
            $this->addError('member_username', 'User is already on this list.');
            return;
        }

        $currentCount = (int) ($this->list->members_count ?? $this->list->members()->count());
        if ($currentCount >= UserList::MAX_MEMBERS) {
            $this->addError('member_username', 'This list already has the maximum number of members ('.UserList::MAX_MEMBERS.').');
            return;
        }

        $changes = $this->list->members()->syncWithoutDetaching([$user->id]);

        if (! $this->list->is_private && in_array($user->id, $changes['attached'] ?? [], true)) {
            if ($user->wantsNotification('lists') && $user->allowsNotificationFrom(Auth::user(), 'lists')) {
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
        return $this->list->members()
            ->orderBy('username')
            ->simplePaginate(
                25,
                ['users.id', 'users.name', 'users.username', 'users.avatar_path', 'users.is_verified'],
                pageName: 'membersPage'
            );
    }

    public function getMemberPreviewProperty()
    {
        return $this->list->members()
            ->orderBy('username')
            ->limit(self::MEMBER_PREVIEW_LIMIT)
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar_path', 'users.is_verified']);
    }

    public function getPostsProperty()
    {
        $memberIdsQuery = $this->list->members()->select('users.id');
        $viewer = Auth::user();

        $query = Post::query()
            ->whereNull('reply_to_id')
            ->where('is_reply_like', false)
            ->whereIn('user_id', $memberIdsQuery)
            ->withPostCardRelations($viewer, true)
            ->latest()
            ->orderByDesc('id');

        if ($viewer) {
            $exclude = $viewer->excludedUserIds();
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
