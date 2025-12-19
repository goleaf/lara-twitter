<?php

use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate(ConfirmPasswordRequest::rulesFor());

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="max-w-md mx-auto">
    <div class="card bg-base-100 border">
        <div class="card-body">
            <h1 class="text-xl font-semibold">Confirm password</h1>
            <p class="text-sm opacity-70">
                {{ __('Please confirm your password before continuing.') }}
            </p>

            <form wire:submit="confirmPassword" class="space-y-4 mt-2">
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end">
                    <x-primary-button>
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
