<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PollDurationRequired implements DataAwareRule, Rule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        $options = $this->data['poll_options'] ?? [];
        if (! is_array($options)) {
            return true;
        }

        $options = array_map('trim', $options);
        $options = array_values(array_filter($options, static fn (string $v): bool => $v !== ''));

        if (count($options) === 0) {
            return true;
        }

        return ! is_null($value);
    }

    public function message(): string
    {
        return 'Poll duration is required when adding a poll.';
    }
}

