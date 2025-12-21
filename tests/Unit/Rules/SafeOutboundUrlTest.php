<?php

namespace Tests\Unit\Rules;

use App\Rules\SafeOutboundUrl;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SafeOutboundUrlTest extends TestCase
{
    private function assertRuleFails(mixed $value): void
    {
        $failed = false;

        (new SafeOutboundUrl())->validate('u', $value, function () use (&$failed): void {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_allows_http_and_https(): void
    {
        $validator = Validator::make(
            ['u' => 'https://example.com'],
            ['u' => [new SafeOutboundUrl()]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_rejects_empty_value(): void
    {
        $this->assertRuleFails('');
    }

    public function test_rejects_non_string_value(): void
    {
        $this->assertRuleFails(['not', 'a', 'string']);
    }

    public function test_rejects_invalid_schemes(): void
    {
        $validator = Validator::make(
            ['u' => 'ftp://example.com'],
            ['u' => [new SafeOutboundUrl()]],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_unparsable_url(): void
    {
        $validator = Validator::make(
            ['u' => 'http:///example.com'],
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
