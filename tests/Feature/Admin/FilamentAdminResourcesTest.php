<?php

namespace Tests\Feature\Admin;

use App\Models\Block;
use App\Models\Bookmark;
use App\Models\ConversationParticipant;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Mention;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\Mute;
use App\Models\PostLinkPreview;
use App\Models\PostPollOption;
use App\Models\PostPollVote;
use App\Models\SpaceReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_new_resource_indexes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Block::factory()->create();
        Bookmark::factory()->create();
        Follow::factory()->create();
        Like::factory()->create();
        Mute::factory()->create();
        ConversationParticipant::factory()->create();
        Mention::factory()->create();
        MessageAttachment::factory()->create();
        MessageReaction::factory()->create();
        PostLinkPreview::factory()->create();
        PostPollOption::factory()->create();
        PostPollVote::factory()->create();
        SpaceReaction::factory()->create();

        $paths = [
            '/admin/blocks',
            '/admin/bookmarks',
            '/admin/follows',
            '/admin/likes',
            '/admin/mutes',
            '/admin/conversation-participants',
            '/admin/mentions',
            '/admin/message-attachments',
            '/admin/message-reactions',
            '/admin/post-link-previews',
            '/admin/post-poll-options',
            '/admin/post-poll-votes',
            '/admin/space-reactions',
        ];

        $this->actingAs($admin);

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_non_admin_cannot_access_new_resource_indexes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $paths = [
            '/admin/blocks',
            '/admin/bookmarks',
            '/admin/follows',
            '/admin/likes',
            '/admin/mutes',
            '/admin/conversation-participants',
            '/admin/mentions',
            '/admin/message-attachments',
            '/admin/message-reactions',
            '/admin/post-link-previews',
            '/admin/post-poll-options',
            '/admin/post-poll-votes',
            '/admin/space-reactions',
        ];

        $this->actingAs($user);

        foreach ($paths as $path) {
            $this->get($path)->assertForbidden();
        }
    }
}
