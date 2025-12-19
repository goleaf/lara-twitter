<?php

namespace Tests\Unit;

use App\Services\PostTextParser;
use PHPUnit\Framework\TestCase;

class PostTextParserTest extends TestCase
{
    public function test_it_extracts_unique_hashtags_and_mentions_case_insensitively(): void
    {
        $parser = new PostTextParser();

        $parsed = $parser->parse('Hello #Laravel #laravel #2025 @John_Doe and @john_doe and @John-Doe and @john-doe');

        $this->assertSame(['laravel', '2025'], $parsed['hashtags']);
        $this->assertSame(['john_doe', 'john-doe'], $parsed['mentions']);
    }
}
