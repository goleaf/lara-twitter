<?php

namespace Tests\Feature;

use App\Services\PostBodyRenderer;
use Tests\TestCase;

class PostBodyRendererTest extends TestCase
{
    public function test_it_uses_redirect_links_when_post_id_is_present(): void
    {
        $url = 'https://example.com/page';
        $postId = 123;

        $html = (string) app(PostBodyRenderer::class)->render("Visit {$url}", $postId);

        $this->assertStringContainsString(
            'href="'.route('links.redirect', ['post' => $postId, 'u' => $url]).'"',
            $html,
        );
    }

    public function test_it_linkifies_urls_mentions_and_hashtags_without_overlapping(): void
    {
        $html = (string) app(PostBodyRenderer::class)->render('Hey @Alice @john-doe check #Laravel https://example.com/#section');

        $this->assertStringContainsString(
            'href="'.route('profile.show', ['user' => 'alice']).'"',
            $html,
        );
        $this->assertStringContainsString(
            'href="'.route('profile.show', ['user' => 'john-doe']).'"',
            $html,
        );
        $this->assertStringContainsString(
            'href="'.route('hashtags.show', ['tag' => 'laravel']).'"',
            $html,
        );
        $this->assertStringContainsString('href="https://example.com/#section"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_it_trims_trailing_punctuation_from_urls(): void
    {
        $html = (string) app(PostBodyRenderer::class)->render('Check (https://example.com).');

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringNotContainsString('href="https://example.com)"', $html);
    }

    public function test_it_trims_complex_trailing_punctuation_from_urls(): void
    {
        $text = 'Check https://example.com/path))]}.,!?:;"\'';

        $html = (string) app(PostBodyRenderer::class)->render($text);

        $this->assertStringContainsString('href="https://example.com/path"', $html);
    }

    public function test_it_uses_cached_rendering_for_identical_input(): void
    {
        $renderer = app(PostBodyRenderer::class);
        $text = 'cache-check-123';

        $first = $renderer->render($text);
        $second = $renderer->render($text);

        $this->assertSame($first, $second);
    }
}
