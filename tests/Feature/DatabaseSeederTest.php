<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_expected_records(): void
    {
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('posts', 3);
        $this->assertDatabaseCount('hashtags', 3);
        $this->assertDatabaseCount('user_lists', 3);
        $this->assertDatabaseCount('conversations', 3);
        $this->assertDatabaseCount('messages', 3);
        $this->assertDatabaseCount('spaces', 3);
        $this->assertDatabaseCount('moments', 3);

        $this->assertDatabaseCount('follows', 3);
        $this->assertDatabaseCount('blocks', 3);
        $this->assertDatabaseCount('mutes', 3);
        $this->assertDatabaseCount('likes', 3);
        $this->assertDatabaseCount('bookmarks', 3);
        $this->assertDatabaseCount('mentions', 3);
        $this->assertDatabaseCount('hashtag_post', 3);
        $this->assertDatabaseCount('post_images', 3);
        $this->assertDatabaseCount('post_polls', 3);
        $this->assertDatabaseCount('post_poll_options', 3);
        $this->assertDatabaseCount('post_poll_votes', 3);
        $this->assertDatabaseCount('post_link_previews', 3);
        $this->assertDatabaseCount('conversation_participants', 7);
        $this->assertDatabaseCount('message_attachments', 3);
        $this->assertDatabaseCount('message_reactions', 3);
        $this->assertDatabaseCount('user_list_user', 3);
        $this->assertDatabaseCount('user_list_subscriptions', 4);
        $this->assertDatabaseCount('muted_terms', 3);
        $this->assertDatabaseCount('space_participants', 6);
        $this->assertDatabaseCount('space_speaker_requests', 3);
        $this->assertDatabaseCount('space_reactions', 3);
        $this->assertDatabaseCount('moment_items', 3);
        $this->assertDatabaseCount('reports', 6);
    }

    protected function beforeRefreshingDatabase()
    {
        RefreshDatabaseState::$migrated = false;
        RefreshDatabaseState::$lazilyRefreshed = false;
        RefreshDatabaseState::$inMemoryConnections = [];

        config([
            'seeding.model_count' => 3,
            'seeding.relation_count' => 3,
        ]);
    }

    protected function tearDown(): void
    {
        RefreshDatabaseState::$migrated = false;
        RefreshDatabaseState::$lazilyRefreshed = false;
        RefreshDatabaseState::$inMemoryConnections = [];

        parent::tearDown();
    }
}
