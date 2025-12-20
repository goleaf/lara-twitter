<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="max-w-md mx-auto space-y-4">
    <div class="text-center">
        <a class="inline-flex items-center gap-3" href="{{ route('timeline') }}" wire:navigate>
            <x-brand-mark />
            <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'MiniTwitter') }}</span>
        </a>
    </div>

    <div class="card bg-base-100 hero-card guest-hero">
        <div class="hero-edge" aria-hidden="true"></div>
        <div class="card-body space-y-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold">Log in</h1>
                <div class="text-sm opacity-70">Welcome back.</div>
            </div>

            <x-auth-session-status :status="session('status')" />

            <form wire:submit="login" class="space-y-4">
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full input-sm" type="email" name="email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full input-sm" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <x-choice-card layout="inline">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="checkbox checkbox-sm" name="remember" wire:loading.attr="disabled" wire:target="login">
                    <span class="text-sm opacity-70">{{ __('Remember me') }}</span>
                </x-choice-card>

                <div class="flex items-center justify-between">
                    @if (Route::has('password.request'))
                        <a class="link link-hover text-sm" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button wire:loading.attr="disabled" wire:target="login">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
