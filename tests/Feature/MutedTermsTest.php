<?php

namespace Tests\Feature;

use App\Models\MutedTerm;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MutedTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_muted_term_hides_posts_from_timeline(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $alice->following()->attach($bob->id);

        Post::query()->create(['user_id' => $bob->id, 'body' => 'Spoiler alert']);
        Post::query()->create(['user_id' => $bob->id, 'body' => 'Safe post']);

        $this->actingAs($alice)->get(route('timeline'))
            ->assertOk()
            ->assertSee('Spoiler alert')
            ->assertSee('Safe post');

        MutedTerm::query()->create([
            'user_id' => $alice->id,
            'term' => 'spoiler',
            'mute_timeline' => true,
            'mute_notifications' => false,
        ]);

        $this->actingAs($alice)->get(route('timeline'))
            ->assertOk()
            ->assertDontSee('Spoiler alert')
            ->assertSee('Safe post');
    }

    public function test_muted_term_hides_matching_notifications_by_excerpt(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        MutedTerm::query()->create([
            'user_id' => $alice->id,
            'term' => 'spoiler',
            'mute_timeline' => false,
            'mute_notifications' => true,
        ]);

        Post::query()->create(['user_id' => $bob->id, 'body' => 'spoiler @alice']);
        Post::query()->create(['user_id' => $bob->id, 'body' => 'hi @alice']);

        $this->actingAs($alice)->get(route('notifications'))
            ->assertOk()
            ->assertSee('mentioned you')
            ->assertDontSee('spoiler');
    }
}

