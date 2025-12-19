<?php

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate(ForgotPasswordRequest::rulesFor());

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="max-w-md mx-auto space-y-4">
    <div class="text-center">
        <a class="inline-flex items-center gap-3" href="{{ route('timeline') }}" wire:navigate>
            <x-brand-mark />
            <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'MiniTwitter') }}</span>
        </a>
    </div>

    <div class="card bg-base-100">
        <div class="card-body space-y-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">Reset password</h1>
                <p class="text-sm opacity-70">
                    {{ __('Enter your email and we’ll send you a reset link.') }}
                </p>
            </div>

            <x-auth-session-status :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="space-y-4">
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" class="block mt-1 w-full input-sm" type="email" name="email" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <a class="link link-hover text-sm" href="{{ route('login') }}" wire:navigate>
                        {{ __('Back to login') }}
                    </a>

                    <x-primary-button>
                        {{ __('Email Password Reset Link') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
