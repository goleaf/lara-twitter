<?php

namespace Tests\Unit\Rules;

use App\Rules\PollDurationRequired;
use PHPUnit\Framework\TestCase;

class PollDurationRequiredTest extends TestCase
{
    public function test_passes_when_no_options(): void
    {
        $rule = new PollDurationRequired();
        $rule->setData(['poll_options' => []]);

        $this->assertTrue($rule->passes('poll_duration', null));
    }

    public function test_passes_when_options_are_not_array(): void
    {
        $rule = new PollDurationRequired();
        $rule->setData(['poll_options' => 'not-an-array']);

        $this->assertTrue($rule->passes('poll_duration', null));
    }

    public function test_fails_when_options_present_and_duration_missing(): void
    {
        $rule = new PollDurationRequired();
        $rule->setData(['poll_options' => ['Option A', 'Option B']]);

        $this->assertFalse($rule->passes('poll_duration', null));
    }

    public function test_passes_when_options_present_and_duration_set(): void
    {
        $rule = new PollDurationRequired();
        $rule->setData(['poll_options' => ['Option A', 'Option B']]);

        $this->assertTrue($rule->passes('poll_duration', 1440));
    }

    public function test_message_is_static(): void
    {
        $rule = new PollDurationRequired();

        $this->assertSame('Poll duration is required when adding a poll.', $rule->message());
    }
}
