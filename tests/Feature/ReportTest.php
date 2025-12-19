<?php

namespace Tests\Feature;

use App\Livewire\ReportButton;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_a_post_once(): void
    {
        $author = User::factory()->create();
        $reporter = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', 'spam')
            ->set('details', 'Looks like automated spam.')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => 'spam',
        ]);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', 'harassment')
            ->call('submit');

        $this->assertDatabaseCount('reports', 1);
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => 'harassment',
        ]);
    }

    public function test_user_cannot_report_their_own_post(): void
    {
        $author = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        Livewire::actingAs($author)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', 'spam')
            ->call('submit')
            ->assertStatus(422);
    }

    public function test_user_can_report_an_account(): void
    {
        $reported = User::factory()->create();
        $reporter = User::factory()->create();

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => User::class,
                'reportableId' => $reported->id,
            ])
            ->set('reason', 'other')
            ->set('details', 'Suspicious behavior')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reported->id,
            'reason' => 'other',
        ]);
    }

    public function test_user_can_report_a_hashtag(): void
    {
        $reporter = User::factory()->create();
        $hashtag = Hashtag::factory()->create(['tag' => 'laravel']);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Hashtag::class,
                'reportableId' => $hashtag->id,
            ])
            ->set('reason', 'spam')
            ->set('details', 'Looks like spam tag.')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Hashtag::class,
            'reportable_id' => $hashtag->id,
            'reason' => 'spam',
        ]);
    }

    public function test_user_cannot_report_themselves(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ReportButton::class, [
                'reportableType' => User::class,
                'reportableId' => $user->id,
            ])
            ->set('reason', 'spam')
            ->call('submit')
            ->assertStatus(422);
    }

    public function test_guest_cannot_report(): void
    {
        $user = User::factory()->create();

        Livewire::test(ReportButton::class, [
            'reportableType' => User::class,
            'reportableId' => $user->id,
        ])
            ->set('reason', 'spam')
            ->call('submit')
            ->assertStatus(403);
    }
}
