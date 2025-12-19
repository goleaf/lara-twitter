<?php

namespace Tests\Feature;

use App\Services\PostBodyRenderer;
use Tests\TestCase;

class PostBodyRendererTest extends TestCase
{
    public function test_it_linkifies_urls_mentions_and_hashtags_without_overlapping(): void
    {
        $html = (string) app(PostBodyRenderer::class)->render('Hey @Alice check #Laravel https://example.com/#section');

        $this->assertStringContainsString(
            'href="'.route('profile.show', ['user' => 'alice']).'"',
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
}
