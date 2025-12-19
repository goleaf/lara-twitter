<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPollOptions implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (! is_array($value)) {
            return true;
        }

        $options = array_map('trim', $value);
        $options = array_values(array_filter($options, static fn (string $v): bool => $v !== ''));

        if (count($options) === 0) {
            return true;
        }

        if (count($options) < 2 || count($options) > 4) {
            return false;
        }

        $normalized = array_map(static fn (string $v): string => mb_strtolower($v), $options);

        return count($normalized) === count(array_unique($normalized));
    }

    public function message(): string
    {
        return 'Poll must have 2–4 unique options.';
    }
}

