<?php

namespace Tests\Unit\Rules;

use App\Rules\SafeOutboundUrl;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SafeOutboundUrlTest extends TestCase
{
    public function test_allows_http_and_https(): void
    {
        $validator = Validator::make(
            ['u' => 'https://example.com'],
            ['u' => [new SafeOutboundUrl()]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_rejects_invalid_schemes(): void
    {
        $validator = Validator::make(
            ['u' => 'ftp://example.com'],
            ['u' => [new SafeOutboundUrl()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_too_long_urls(): void
    {
        $tooLong = 'https://example.com/'.str_repeat('a', 2050);

        $validator = Validator::make(
            ['u' => $tooLong],
            ['u' => [new SafeOutboundUrl()]],
        );

        $this->assertTrue($validator->fails());
    }
}
