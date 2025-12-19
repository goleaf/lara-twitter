<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\MutedTermMatcher;
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
        $this->tab = $this->normalizedTab();
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

        $items = $this->applyMutedTermsFilter($items);

        if ($this->normalizedTab() === 'verified') {
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

        $verifiedIds = \App\Models\User::query()
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

    private function applyMutedTermsFilter(Collection $items): Collection
    {
        $terms = Auth::user()
            ->mutedTerms()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('mute_notifications', true)
            ->latest()
            ->limit(50)
            ->get();

        if ($terms->isEmpty()) {
            return $items;
        }

        $matcher = app(MutedTermMatcher::class);

        return $items
            ->filter(function ($notification) use ($terms, $matcher) {
                $excerpt = (string) (($notification->data ?? [])['excerpt'] ?? '');
                if ($excerpt === '') {
                    return true;
                }

                foreach ($terms as $term) {
                    if ($matcher->matches($excerpt, $term)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.notifications-page')->layout('layouts.app');
    }
}
