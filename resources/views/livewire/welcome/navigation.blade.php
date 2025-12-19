<nav class="-mx-2 flex flex-1 justify-end gap-2">
    @auth
        <a class="btn btn-ghost btn-sm" href="{{ url('/dashboard') }}" wire:navigate>Dashboard</a>
    @else
        <a class="btn btn-ghost btn-sm" href="{{ route('login') }}" wire:navigate>Log in</a>

        @if (Route::has('register'))
            <a class="btn btn-outline btn-sm" href="{{ route('register') }}" wire:navigate>Register</a>
        @endif
    @endauth
</nav>
