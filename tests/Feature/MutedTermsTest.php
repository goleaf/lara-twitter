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

    public function test_muted_term_hides_reposts_when_original_contains_term(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $carol = User::factory()->create(['username' => 'carol']);

        $alice->following()->attach($carol->id);

        $original = Post::query()->create(['user_id' => $bob->id, 'body' => 'Spoiler alert']);

        Post::query()->create([
            'user_id' => $carol->id,
            'repost_of_id' => $original->id,
            'body' => '',
        ]);

        $this->actingAs($alice)->get(route('timeline'))
            ->assertOk()
            ->assertSee('Spoiler alert');

        MutedTerm::query()->create([
            'user_id' => $alice->id,
            'term' => 'spoiler',
            'mute_timeline' => true,
            'mute_notifications' => false,
        ]);

        $this->actingAs($alice)->get(route('timeline'))
            ->assertOk()
            ->assertDontSee('Spoiler alert');
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

    public function test_muted_term_only_non_followed_does_not_hide_notifications_from_followed_users(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $alice->following()->attach($bob->id);

        MutedTerm::query()->create([
            'user_id' => $alice->id,
            'term' => 'spoiler',
            'only_non_followed' => true,
            'mute_timeline' => false,
            'mute_notifications' => true,
        ]);

        Post::query()->create(['user_id' => $bob->id, 'body' => 'spoiler @alice']);

        $this->actingAs($alice)->get(route('notifications'))
            ->assertOk()
            ->assertSee('mentioned you')
            ->assertSee('spoiler');
    }

    public function test_muted_term_hidden_notifications_do_not_increment_unread_badge_count(): void
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

        $this->actingAs($alice)->get(route('timeline'))
            ->assertOk()
            ->assertDontSee('indicator-item badge badge-primary badge-sm');
    }
}
