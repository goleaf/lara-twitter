<?php

namespace Tests\Feature;

use App\Livewire\PostComposer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PremiumPostLengthTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_premium_is_limited_to_280_characters(): void
    {
        $user = User::factory()->create(['is_premium' => false]);

        Livewire::actingAs($user)
            ->test(PostComposer::class)
            ->set('body', str_repeat('a', 281))
            ->call('save')
            ->assertHasErrors(['body' => 'max']);
    }

    public function test_premium_can_post_longer_text(): void
    {
        $user = User::factory()->create(['is_premium' => true]);

        Livewire::actingAs($user)
            ->test(PostComposer::class)
            ->set('body', str_repeat('a', 500))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'body' => str_repeat('a', 500),
        ]);
    }
}

