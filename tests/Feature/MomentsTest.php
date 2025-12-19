<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MomentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moments_index_requires_auth(): void
    {
        $this->get(route('moments.index'))->assertRedirect('/login');
    }

    public function test_user_can_create_moment_and_add_posts_in_order(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $author = User::factory()->create(['username' => 'alice']);

        $p1 = Post::query()->create(['user_id' => $author->id, 'body' => 'First']);
        $p2 = Post::query()->create(['user_id' => $author->id, 'body' => 'Second']);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentsPage::class)
            ->set('title', 'My Moment')
            ->set('description', 'Desc')
            ->set('is_public', true)
            ->call('create')
            ->assertRedirect();

        $moment = Moment::query()->firstOrFail();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('post_id', (string) $p1->id)
            ->call('addPost')
            ->assertHasNoErrors();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('post_id', (string) $p2->id)
            ->call('addPost')
            ->assertHasNoErrors();

        $response = $this->get(route('moments.show', $moment));

        $response->assertOk()->assertSeeInOrder(['First', 'Second']);
    }

    public function test_private_moment_visible_only_to_owner(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $other = User::factory()->create(['username' => 'other']);

        $moment = Moment::query()->create([
            'owner_id' => $owner->id,
            'title' => 'Private',
            'is_public' => false,
        ]);

        $this->get(route('moments.show', $moment))->assertForbidden();
        $this->actingAs($other)->get(route('moments.show', $moment))->assertForbidden();
        $this->actingAs($owner)->get(route('moments.show', $moment))->assertOk();
    }
}

