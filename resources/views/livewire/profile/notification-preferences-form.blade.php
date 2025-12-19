<?php

use App\Http\Requests\Profile\UpdateNotificationPreferencesRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $likes = true;
    public bool $reposts = true;
    public bool $replies = true;
    public bool $mentions = true;
    public bool $follows = true;
    public bool $dms = true;
    public bool $lists = true;
    public bool $followed_posts = false;
    public bool $high_engagement = false;

    public bool $quality_filter = false;
    public bool $only_following = false;
    public bool $only_verified = false;

    public bool $email_enabled = false;
    public bool $quiet_hours_enabled = false;
    public string $quiet_hours_start = '22:00';
    public string $quiet_hours_end = '07:00';

    public function mount(): void
    {
        $user = Auth::user();
        $settings = $user->notification_settings ?? [];

        $this->likes = (bool) ($settings['likes'] ?? true);
        $this->reposts = (bool) ($settings['reposts'] ?? true);
        $this->replies = (bool) ($settings['replies'] ?? true);
        $this->mentions = (bool) ($settings['mentions'] ?? true);
        $this->follows = (bool) ($settings['follows'] ?? true);
        $this->dms = (bool) ($settings['dms'] ?? true);
        $this->lists = (bool) ($settings['lists'] ?? true);
        $this->followed_posts = (bool) ($settings['followed_posts'] ?? false);
        $this->high_engagement = (bool) ($settings['high_engagement'] ?? false);

        $this->quality_filter = (bool) ($settings['quality_filter'] ?? false);
        $this->only_following = (bool) ($settings['only_following'] ?? false);
        $this->only_verified = (bool) ($settings['only_verified'] ?? false);

        $this->email_enabled = (bool) ($settings['email_enabled'] ?? false);
        $this->quiet_hours_enabled = (bool) ($settings['quiet_hours_enabled'] ?? false);
        $this->quiet_hours_start = (string) ($settings['quiet_hours_start'] ?? '22:00');
        $this->quiet_hours_end = (string) ($settings['quiet_hours_end'] ?? '07:00');
    }

    public function save(): void
    {
        $validated = $this->validate(UpdateNotificationPreferencesRequest::rulesFor());

        $user = Auth::user();
        $existing = $user->notification_settings ?? [];
        $user->notification_settings = array_merge($existing, [
            'likes' => (bool) ($validated['likes'] ?? false),
            'reposts' => (bool) ($validated['reposts'] ?? false),
            'replies' => (bool) ($validated['replies'] ?? false),
            'mentions' => (bool) ($validated['mentions'] ?? false),
            'follows' => (bool) ($validated['follows'] ?? false),
            'dms' => (bool) ($validated['dms'] ?? false),
            'lists' => (bool) ($validated['lists'] ?? false),
            'followed_posts' => (bool) ($validated['followed_posts'] ?? false),
            'high_engagement' => (bool) ($validated['high_engagement'] ?? false),
            'quality_filter' => (bool) ($validated['quality_filter'] ?? false),
            'only_following' => (bool) ($validated['only_following'] ?? false),
            'only_verified' => (bool) ($validated['only_verified'] ?? false),
            'email_enabled' => (bool) ($validated['email_enabled'] ?? false),
            'quiet_hours_enabled' => (bool) ($validated['quiet_hours_enabled'] ?? false),
            'quiet_hours_start' => (string) ($validated['quiet_hours_start'] ?? '22:00'),
            'quiet_hours_end' => (string) ($validated['quiet_hours_end'] ?? '07:00'),
        ]);
        $user->save();

        $this->dispatch('notification-preferences-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notifications') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Choose which activity should notify you.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-4">
        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="likes" />
            <span class="text-sm">{{ __('Likes') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="reposts" />
            <span class="text-sm">{{ __('Retweets / Quotes') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="replies" />
            <span class="text-sm">{{ __('Replies') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="mentions" />
            <span class="text-sm">{{ __('Mentions') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="follows" />
            <span class="text-sm">{{ __('New followers') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="dms" />
            <span class="text-sm">{{ __('Direct messages') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="lists" />
            <span class="text-sm">{{ __('Lists (added to a public list)') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="followed_posts" />
            <span class="text-sm">{{ __('Posts from people you follow') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="high_engagement" />
            <span class="text-sm">{{ __('Significant engagement ("getting more attention than usual")') }}</span>
        </label>

        <div class="divider my-2"></div>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="email_enabled" />
            <span class="text-sm">{{ __('Email alerts') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="quiet_hours_enabled" />
            <span class="text-sm">{{ __('Quiet hours (pause email alerts)') }}</span>
        </label>

        @if ($quiet_hours_enabled)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="label">
                        <span class="label-text">From</span>
                    </label>
                    <input type="time" class="input input-bordered w-full" wire:model="quiet_hours_start" />
                    <x-input-error class="mt-2" :messages="$errors->get('quiet_hours_start')" />
                </div>

                <div>
                    <label class="label">
                        <span class="label-text">To</span>
                    </label>
                    <input type="time" class="input input-bordered w-full" wire:model="quiet_hours_end" />
                    <x-input-error class="mt-2" :messages="$errors->get('quiet_hours_end')" />
                </div>
            </div>
        @endif

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="quality_filter" />
            <span class="text-sm">{{ __('Quality filter (requires avatar + verified email)') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="only_following" />
            <span class="text-sm">{{ __('Only notify from accounts you follow') }}</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" class="checkbox checkbox-sm" wire:model="only_verified" />
            <span class="text-sm">{{ __('Only notify from verified accounts') }}</span>
        </label>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="notification-preferences-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
