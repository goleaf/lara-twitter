<?php

namespace Tests\Unit\Models;

use App\Models\PostLinkPreview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_postLinkPreview_can_be_created(): void
    {
        $postLinkPreview = PostLinkPreview::factory()->create();

        $this->assertInstanceOf(PostLinkPreview::class, $postLinkPreview);
        $this->assertDatabaseHas('post_link_previews', [
            'id' => $postLinkPreview->id,
        ]);
    }

    public function test_postLinkPreview_has_factory(): void
    {
        $postLinkPreview = PostLinkPreview::factory()->make();

        $this->assertInstanceOf(PostLinkPreview::class, $postLinkPreview);
    }
}