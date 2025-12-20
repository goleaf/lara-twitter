<?php

namespace Tests\Unit\Services;

use App\Models\MutedTerm;
use App\Services\MutedTermMatcher;
use PHPUnit\Framework\TestCase;

class MutedTermMatcherTest extends TestCase
{
    public function test_returns_false_for_empty_term(): void
    {
        $service = new MutedTermMatcher();
        $term = new MutedTerm(['term' => '', 'whole_word' => false]);

        $this->assertFalse($service->matches('anything', $term));
    }

    public function test_matches_hashtag_terms_case_insensitively(): void
    {
        $service = new MutedTermMatcher();
        $term = new MutedTerm(['term' => '#Laravel', 'whole_word' => false]);

        $this->assertTrue($service->matches('I love #laravel', $term));
    }

    public function test_respects_whole_word_matching(): void
    {
        $service = new MutedTermMatcher();
        $term = new MutedTerm(['term' => 'cat', 'whole_word' => true]);

        $this->assertTrue($service->matches('a cat walks', $term));
        $this->assertFalse($service->matches('concatenate', $term));
    }

    public function test_falls_back_to_substring_matching(): void
    {
        $service = new MutedTermMatcher();
        $term = new MutedTerm(['term' => 'cat', 'whole_word' => false]);

        $this->assertTrue($service->matches('concatenate', $term));
    }
}
