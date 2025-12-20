<?php

namespace Tests\Unit\Rules;

use App\Rules\ExclusivePostMedia;
use PHPUnit\Framework\TestCase;

class ExclusivePostMediaTest extends TestCase
{
    public function test_passes_when_only_one_field_has_media(): void
    {
        $rule = new ExclusivePostMedia('poll_options');
        $rule->setData(['poll_options' => []]);

        $this->assertTrue($rule->passes('media', ['file']));
    }

    public function test_fails_when_both_fields_have_media(): void
    {
        $rule = new ExclusivePostMedia('poll_options');
        $rule->setData(['poll_options' => ['choice']]);

        $this->assertFalse($rule->passes('media', ['file']));
    }

    public function test_empty_arrays_are_not_media(): void
    {
        $rule = new ExclusivePostMedia('poll_options');
        $rule->setData(['poll_options' => []]);

        $this->assertTrue($rule->passes('media', []));
        $this->assertTrue($rule->passes('media', [null]));
    }

    public function test_message_is_static(): void
    {
        $rule = new ExclusivePostMedia('poll_options');

        $this->assertSame('Choose only one attachment type.', $rule->message());
    }
}
