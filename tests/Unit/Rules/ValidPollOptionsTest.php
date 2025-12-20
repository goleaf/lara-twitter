<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidPollOptions;
use PHPUnit\Framework\TestCase;

class ValidPollOptionsTest extends TestCase
{
    public function test_passes_when_value_is_not_array(): void
    {
        $rule = new ValidPollOptions();

        $this->assertTrue($rule->passes('poll_options', 'not-an-array'));
    }

    public function test_passes_when_no_options_provided(): void
    {
        $rule = new ValidPollOptions();

        $this->assertTrue($rule->passes('poll_options', []));
        $this->assertTrue($rule->passes('poll_options', ['   ']));
    }

    public function test_fails_when_too_few_or_too_many_options(): void
    {
        $rule = new ValidPollOptions();

        $this->assertFalse($rule->passes('poll_options', ['Only one']));
        $this->assertFalse($rule->passes('poll_options', ['1', '2', '3', '4', '5']));
    }

    public function test_fails_when_options_are_not_unique_case_insensitive(): void
    {
        $rule = new ValidPollOptions();

        $this->assertFalse($rule->passes('poll_options', ['Option', 'option']));
    }

    public function test_passes_with_two_to_four_unique_options(): void
    {
        $rule = new ValidPollOptions();

        $this->assertTrue($rule->passes('poll_options', ['A', 'B']));
        $this->assertTrue($rule->passes('poll_options', ['A', 'B', 'C', 'D']));
    }
}
