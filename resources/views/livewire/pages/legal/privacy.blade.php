<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
}; ?>

<x-slot:header>
    <div class="text-xl font-semibold">Privacy</div>
</x-slot:header>

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="card bg-base-100 border hero-card legal-hero">
            <div class="hero-edge" aria-hidden="true"></div>
            <div class="card-body">
                <div class="text-[0.65rem] uppercase tracking-[0.35em] text-base-content/60">Legal</div>
                <div class="text-2xl font-semibold">Privacy Policy</div>
                <p class="text-sm opacity-70 max-w-2xl">
                    This policy explains what data we collect, why we collect it, and the choices you have.
                </p>
                <div class="text-xs opacity-60">We only use data to run and improve the product.</div>
            </div>
        </div>

        <div class="card bg-base-100 border">
            <div class="card-body prose max-w-none">
                <h2>Data we collect</h2>
                <ul>
                    <li>Account details like name, username, and email.</li>
                    <li>Content you post, including media and profile updates.</li>
                    <li>Usage signals such as likes, replies, and link clicks.</li>
                </ul>

                <h2>How we use data</h2>
                <ul>
                    <li>Deliver the service, including timelines, search, and direct messages.</li>
                    <li>Keep the community safe and reduce abuse.</li>
                    <li>Understand feature usage so we can improve the experience.</li>
                </ul>

                <h2>Sharing</h2>
                <p>
                    We do not sell personal data. We only share information when needed to operate the service or
                    when required by law.
                </p>

                <h2>Retention</h2>
                <p>
                    We keep data for as long as your account is active or as needed to comply with legal
                    requirements. You can delete your account to remove your profile and posts.
                </p>

                <h2>Your choices</h2>
                <p>
                    You can update your profile, change notification settings, or disable analytics from the
                    settings page.
                </p>
            </div>
        </div>
    </div>
