<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\NotificationVisibilityService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'all';

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
        $this->tab = $this->currentTab();
    }

    public function open(string $notificationId): void
    {
        abort_unless(Auth::check(), 403);

        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $data = $notification->data ?? [];
        $type = $data['type'] ?? null;

        $postId = $data['post_id'] ?? $data['original_post_id'] ?? null;
        $conversationId = $data['conversation_id'] ?? null;
        $profileUsername = $data['follower_username'] ?? $data['actor_username'] ?? null;

        $href = route('notifications');

        if ($type === 'message_received' && $conversationId) {
            $href = route('messages.show', $conversationId);
        } elseif ($type === 'user_followed' && $profileUsername) {
            $href = route('profile.show', ['user' => $profileUsername]);
        } elseif ($type === 'added_to_list' && ($data['list_id'] ?? null)) {
            $href = route('lists.show', $data['list_id']);
        } elseif ($postId) {
            $href = route('posts.show', $postId);
        }

        $this->redirect($href, navigate: true);
    }

    public function markAllRead(): void
    {
        abort_unless(Auth::check(), 403);

        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        $this->dispatch('$refresh');
    }

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->tab = $this->normalizedTab();
    }

    private function normalizedTab(): string
    {
        return in_array($this->tab, ['all', 'verified'], true) ? $this->tab : 'all';
    }

    private function currentTab(): string
    {
        $tab = request()->query('tab', $this->tab);

        return in_array($tab, ['all', 'verified'], true) ? $tab : 'all';
    }

    private function actorUserId(array $data): ?int
    {
        $id = Arr::get($data, 'actor_user_id');

        return is_numeric($id) ? (int) $id : null;
    }

    public function getNotificationsProperty()
    {
        $items = Auth::user()
            ->notifications()
            ->latest()
            ->limit(200)
            ->get();

        $items = app(NotificationVisibilityService::class)->filter(Auth::user(), $items);

        if ($this->currentTab() === 'verified') {
            $items = $this->applyVerifiedFilter($items);
        }

        $perPage = 30;
        $page = $this->getPage();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    private function applyVerifiedFilter(Collection $items): Collection
    {
        $actorIds = $items
            ->map(fn ($n) => $this->actorUserId($n->data ?? []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $verifiedIds = User::query()
            ->whereIn('id', $actorIds ?: [-1])
            ->where('is_verified', true)
            ->pluck('id')
            ->all();

        return $items
            ->filter(function ($n) use ($verifiedIds) {
                $id = $this->actorUserId($n->data ?? []);
                return $id && in_array($id, $verifiedIds, true);
            })
            ->values();
    }

    public function render()
    {
        $notifications = $this->notifications;

        $actorIds = collect($notifications->items())
            ->map(fn ($n) => $this->actorUserId($n->data ?? []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $actorUsers = User::query()
            ->whereIn('id', $actorIds ?: [-1])
            ->get()
            ->keyBy('id');

        return view('livewire.notifications-page', [
            'actorUsers' => $actorUsers,
        ])->layout('layouts.app');
    }
}
