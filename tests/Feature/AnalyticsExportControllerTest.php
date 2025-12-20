<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_requires_auth(): void
    {
        $this->get(route('analytics.export'))
            ->assertStatus(403);
    }

    public function test_export_requires_analytics_permission(): void
    {
        $user = User::factory()->create(['analytics_enabled' => false, 'is_admin' => false]);

        $this->actingAs($user)
            ->get(route('analytics.export'))
            ->assertStatus(403);
    }

    public function test_export_returns_csv_with_counts(): void
    {
        $user = User::factory()->create(['analytics_enabled' => true]);

        $post = Post::factory()->for($user)->create([
            'body' => 'Hello world',
            'created_at' => now()->subDay(),
        ]);

        Like::factory()->create(['post_id' => $post->id, 'created_at' => now()]);
        Like::factory()->create(['post_id' => $post->id, 'created_at' => now()]);

        Post::factory()->create([
            'repost_of_id' => $post->id,
            'created_at' => now(),
        ]);

        Post::factory()->create([
            'reply_to_id' => $post->id,
            'created_at' => now(),
        ]);

        $today = now()->toDateString();

        DB::table('analytics_uniques')->insert([
            [
                'type' => 'post_view',
                'entity_id' => $post->id,
                'day' => $today,
                'viewer_key' => 'viewer-a',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'post_view',
                'entity_id' => $post->id,
                'day' => $today,
                'viewer_key' => 'viewer-b',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'post_link_click',
                'entity_id' => $post->id,
                'day' => $today,
                'viewer_key' => 'viewer-c',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'post_profile_click',
                'entity_id' => $post->id,
                'day' => $today,
                'viewer_key' => 'viewer-d',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'post_media_view',
                'entity_id' => $post->id,
                'day' => $today,
                'viewer_key' => 'viewer-e',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('analytics.export', ['range' => '7d']));

        $response->assertOk();
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $lines = array_values(array_filter(explode("\n", trim($content))));
        $rows = array_map('str_getcsv', $lines);

        $this->assertSame([
            'post_id',
            'created_at',
            'post_url',
            'body',
            'impressions',
            'engagements',
            'engagement_rate',
            'link_clicks',
            'profile_clicks',
            'media_views',
            'likes',
            'reposts',
            'replies',
        ], $rows[0]);

        $row = $rows[1];

        $this->assertSame((string) $post->id, $row[0]);
        $this->assertSame('Hello world', $row[3]);
        $this->assertSame('2', $row[4]);
        $this->assertSame('7', $row[5]);
        $this->assertSame('350.0', $row[6]);
        $this->assertSame('1', $row[7]);
        $this->assertSame('1', $row[8]);
        $this->assertSame('1', $row[9]);
        $this->assertSame('2', $row[10]);
        $this->assertSame('1', $row[11]);
        $this->assertSame('1', $row[12]);
    }
}
