<?php

namespace Tests\Feature;

use App\Livewire\ReportButton;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Hashtag;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_details_are_required_for_serious_reasons(): void
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
            ->set('reason', Report::REASON_VIOLENCE)
            ->call('submit')
            ->assertHasErrors(['details']);
    }

    public function test_report_submit_shows_case_number_notice(): void
    {
        $author = User::factory()->create();
        $reporter = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'body' => 'Hello world',
        ]);

        $component = Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Post::class,
                'reportableId' => $post->id,
            ])
            ->set('reason', Report::REASON_SPAM)
            ->set('details', 'Looks like automated spam.')
            ->call('submit');

        $report = Report::query()->firstOrFail();

        $component
            ->assertSee('Report submitted')
            ->assertSee($report->case_number);
    }

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

    public function test_user_can_report_a_list(): void
    {
        $owner = User::factory()->create(['username' => 'owner']);
        $reporter = User::factory()->create(['username' => 'reporter']);

        $list = UserList::query()->create([
            'owner_id' => $owner->id,
            'name' => 'My list',
            'is_private' => false,
        ]);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => UserList::class,
                'reportableId' => $list->id,
            ])
            ->set('reason', 'other')
            ->set('details', 'Abusive list name')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => UserList::class,
            'reportable_id' => $list->id,
            'reason' => 'other',
        ]);
    }

    public function test_user_can_report_a_space(): void
    {
        $host = User::factory()->create(['username' => 'host']);
        $reporter = User::factory()->create(['username' => 'reporter']);

        $space = Space::query()->create([
            'host_user_id' => $host->id,
            'title' => 'Space',
        ]);

        Livewire::actingAs($reporter)
            ->test(ReportButton::class, [
                'reportableType' => Space::class,
                'reportableId' => $space->id,
            ])
            ->set('reason', 'harassment')
            ->set('details', 'Harassment in space')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Space::class,
            'reportable_id' => $space->id,
            'reason' => 'harassment',
        ]);
    }

    public function test_user_can_report_a_message_in_a_conversation_they_are_in(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);

        $conversation = Conversation::query()->create([
            'created_by_user_id' => $alice->id,
            'is_group' => false,
        ]);

        ConversationParticipant::query()->create(['conversation_id' => $conversation->id, 'user_id' => $alice->id]);
        ConversationParticipant::query()->create(['conversation_id' => $conversation->id, 'user_id' => $bob->id]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $bob->id,
            'body' => 'Abusive message',
        ]);

        Livewire::actingAs($alice)
            ->test(ReportButton::class, [
                'reportableType' => Message::class,
                'reportableId' => $message->id,
            ])
            ->set('reason', 'harassment')
            ->set('details', 'Harassment in DM')
            ->call('submit');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $alice->id,
            'reportable_type' => Message::class,
            'reportable_id' => $message->id,
            'reason' => 'harassment',
        ]);
    }

    public function test_user_cannot_report_message_outside_their_conversation(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob = User::factory()->create(['username' => 'bob']);
        $mallory = User::factory()->create(['username' => 'mallory']);

        $conversation = Conversation::query()->create([
            'created_by_user_id' => $alice->id,
            'is_group' => false,
        ]);

        ConversationParticipant::query()->create(['conversation_id' => $conversation->id, 'user_id' => $alice->id]);
        ConversationParticipant::query()->create(['conversation_id' => $conversation->id, 'user_id' => $bob->id]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $bob->id,
            'body' => 'Hello',
        ]);

        Livewire::actingAs($mallory)
            ->test(ReportButton::class, [
                'reportableType' => Message::class,
                'reportableId' => $message->id,
            ])
            ->set('reason', 'spam')
            ->call('submit')
            ->assertStatus(403);
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
