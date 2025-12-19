<?php

namespace Tests\Feature;

use App\Livewire\ProfilePage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlockMuteTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_prevents_viewing_profile_following_and_dm(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        Livewire::actingAs($alice)
            ->test(ProfilePage::class, ['user' => $bob])
            ->call('toggleBlock')
            ->assertHasNoErrors();

        $this->actingAs($bob)->get(route('profile.show', ['user' => $alice]))->assertForbidden();

        $this->actingAs($bob)->get(route('messages.new', ['user' => $alice]))->assertForbidden();
    }

    public function test_mute_hides_user_posts_from_timeline(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $bob->following()->attach($alice->id);

        Post::query()->create(['user_id' => $alice->id, 'body' => 'From alice']);

        $this->actingAs($bob)->get(route('timeline'))->assertOk()->assertSee('From alice');

        Livewire::actingAs($bob)
            ->test(ProfilePage::class, ['user' => $alice])
            ->call('toggleMute')
            ->assertHasNoErrors();

        $this->actingAs($bob)->get(route('timeline'))->assertOk()->assertDontSee('From alice');
    }
}
