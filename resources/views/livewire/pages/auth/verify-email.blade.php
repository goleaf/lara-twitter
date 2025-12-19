<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="max-w-md mx-auto space-y-4">
    <div class="text-center">
        <a class="inline-flex items-center gap-3" href="{{ route('timeline') }}" wire:navigate>
            <x-brand-mark class="h-11 w-11" />
            <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'MiniTwitter') }}</span>
        </a>
    </div>

    <div class="card bg-base-100">
        <div class="card-body space-y-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">Verify your email</h1>
                <p class="text-sm opacity-70">
                    {{ __('Check your inbox for a verification link. If you didn’t get it, resend below.') }}
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    <span>{{ __('A new verification link has been sent to your email address.') }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <x-primary-button wire:click="sendVerification">
                    {{ __('Resend Verification Email') }}
                </x-primary-button>

                <button wire:click="logout" type="button" class="btn btn-ghost btn-sm">
                    {{ __('Log Out') }}
                </button>
            </div>
        </div>
    </div>
</div>
