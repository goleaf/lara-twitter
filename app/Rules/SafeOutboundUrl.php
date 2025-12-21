<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeOutboundUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute field must be a valid URL.');

            return;
        }

        if (strlen($value) > 2048) {
            $fail('The :attribute field must be a valid URL.');

            return;
        }

        $parts = parse_url($value);
        if (! is_array($parts)) {
            $fail('The :attribute field must be a valid URL.');

            return;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            $fail('The :attribute field must be a valid URL.');
        }
    }
}
