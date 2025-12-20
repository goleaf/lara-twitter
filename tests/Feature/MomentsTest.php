<?php

namespace Tests\Feature;

use App\Models\Moment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MomentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_moments_index_is_public(): void
    {
        $this->get(route('moments.index'))->assertOk()->assertSee('Moments');
    }

    public function test_user_can_create_moment_and_add_posts_in_order(): void
    {
        $owner = User::factory()->create(['username' => 'owner', 'is_verified' => true]);
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

    public function test_moment_can_have_cover_image(): void
    {
        Storage::persistentFake('public');

        $owner = User::factory()->create(['username' => 'owner', 'is_verified' => true]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentsPage::class)
            ->set('title', 'My Moment')
            ->set('cover_image', UploadedFile::fake()->image('cover.jpg', 1200, 630))
            ->call('create')
            ->assertRedirect();

        $moment = Moment::query()->firstOrFail();
        $this->assertNotNull($moment->cover_image_path);
        Storage::disk('public')->assertExists($moment->cover_image_path);
    }

    public function test_owner_can_update_moment_cover_image(): void
    {
        Storage::persistentFake('public');

        $owner = User::factory()->create(['username' => 'owner', 'is_verified' => true]);

        $moment = Moment::query()->create([
            'owner_id' => $owner->id,
            'title' => 'Moment',
            'description' => null,
            'is_public' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('cover_image', UploadedFile::fake()->image('new-cover.jpg', 1200, 630))
            ->call('updateMoment')
            ->assertHasNoErrors();

        $moment->refresh();

        $this->assertNotNull($moment->cover_image_path);
        Storage::disk('public')->assertExists($moment->cover_image_path);
    }

    public function test_unverified_user_cannot_create_moment(): void
    {
        $user = User::factory()->create(['username' => 'user', 'is_verified' => false]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\MomentsPage::class)
            ->set('title', 'Nope')
            ->call('create')
            ->assertStatus(403);

        $this->assertDatabaseCount('moments', 0);
    }

    public function test_moment_item_can_have_caption(): void
    {
        $owner = User::factory()->create(['username' => 'owner', 'is_verified' => true]);
        $author = User::factory()->create(['username' => 'alice']);

        $post = Post::query()->create(['user_id' => $author->id, 'body' => 'Hello']);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentsPage::class)
            ->set('title', 'My Moment')
            ->call('create')
            ->assertRedirect();

        $moment = Moment::query()->firstOrFail();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('post_id', (string) $post->id)
            ->set('caption', 'Context')
            ->call('addPost')
            ->assertHasNoErrors();

        $item = $moment->refresh()->items()->firstOrFail();
        $this->assertSame('Context', $item->caption);

        $this->get(route('moments.show', $moment))
            ->assertOk()
            ->assertSee('Context')
            ->assertSee('Hello');
    }

    public function test_owner_can_reorder_items(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $author = User::factory()->create(['username' => 'alice']);

        $p1 = Post::query()->create(['user_id' => $author->id, 'body' => 'First']);
        $p2 = Post::query()->create(['user_id' => $author->id, 'body' => 'Second']);

        $moment = Moment::query()->create([
            'owner_id' => $owner->id,
            'title' => 'Moment',
            'is_public' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('post_id', (string) $p1->id)
            ->call('addPost');

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->set('post_id', (string) $p2->id)
            ->call('addPost');

        $secondItemId = $moment->refresh()->items()->where('post_id', $p2->id)->firstOrFail()->id;

        Livewire::actingAs($owner)
            ->test(\App\Livewire\MomentPage::class, ['moment' => $moment])
            ->call('moveItemUp', $secondItemId)
            ->assertHasNoErrors();

        $response = $this->get(route('moments.show', $moment));
        $response->assertOk()->assertSeeInOrder(['Second', 'First']);
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
