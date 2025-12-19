<?php

use App\Http\Requests\Profile\UpdateProfileInformationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $bio = '';
    public string $location = '';
    public string $website = '';
    public ?string $birth_date = null;
    public string $birth_date_visibility = User::BIRTH_DATE_PUBLIC;
    public $avatar;
    public $header;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->username = Auth::user()->username;
        $this->email = Auth::user()->email;
        $this->bio = Auth::user()->bio ?? '';
        $this->location = Auth::user()->location ?? '';
        $this->website = Auth::user()->website ?? '';
        $this->birth_date = Auth::user()->birth_date?->format('Y-m-d');
        $this->birth_date_visibility = Auth::user()->birth_date_visibility ?? User::BIRTH_DATE_PUBLIC;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if ($this->birth_date === '') {
            $this->birth_date = null;
        }

        $validated = $this->validate(UpdateProfileInformationRequest::rulesFor($user));

        if (! empty($validated['avatar'])) {
            $path = $validated['avatar']->storePublicly("avatars/{$user->id}", ['disk' => 'public']);
            $validated['avatar_path'] = $path;
            unset($validated['avatar']);
        }

        if (! empty($validated['header'])) {
            $path = $validated['header']->storePublicly("headers/{$user->id}", ['disk' => 'public']);
            $validated['header_path'] = $path;
            unset($validated['header']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-base-content">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm opacity-70">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="header" :value="__('Header image')" />
            <input wire:model="header" id="header" name="header" type="file" class="file-input file-input-bordered file-input-sm w-full mt-1" />
            <x-input-error class="mt-2" :messages="$errors->get('header')" />
        </div>

        <div class="flex items-center gap-4">
            <div class="avatar">
                    <div class="w-16 rounded-full">
                        @if (auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="" loading="lazy" decoding="async" />
                        @else
                            <div class="bg-base-200 grid place-items-center h-full w-full text-lg font-semibold">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                </div>
            </div>

            <div class="w-full">
                <x-input-label for="avatar" :value="__('Avatar')" />
                <input wire:model="avatar" id="avatar" name="avatar" type="file" class="file-input file-input-bordered file-input-sm w-full mt-1" />
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full input-sm" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input wire:model="username" id="username" name="username" type="text" class="mt-1 block w-full input-sm" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full input-sm" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="link link-primary text-sm">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea wire:model="bio" id="bio" name="bio" class="textarea textarea-bordered textarea-sm mt-1 block w-full" rows="3"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <x-input-label for="location" :value="__('Location')" />
            <x-text-input wire:model="location" id="location" name="location" type="text" class="mt-1 block w-full input-sm" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('location')" />
        </div>

        <div>
            <x-input-label for="website" :value="__('Website')" />
            <x-text-input wire:model="website" id="website" name="website" type="url" class="mt-1 block w-full input-sm" placeholder="https://example.com" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('website')" />
        </div>

        <div>
            <x-input-label for="birth_date" :value="__('Birth date')" />
            <input wire:model="birth_date" id="birth_date" name="birth_date" type="date" class="input input-bordered input-sm mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>

        <div>
            <x-input-label for="birth_date_visibility" :value="__('Birth date visibility')" />
            <select wire:model="birth_date_visibility" id="birth_date_visibility" class="select select-bordered select-sm w-full mt-1">
                <option value="{{ \App\Models\User::BIRTH_DATE_PUBLIC }}">Public</option>
                <option value="{{ \App\Models\User::BIRTH_DATE_FOLLOWERS }}">Followers</option>
                <option value="{{ \App\Models\User::BIRTH_DATE_PRIVATE }}">Only you</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('birth_date_visibility')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
