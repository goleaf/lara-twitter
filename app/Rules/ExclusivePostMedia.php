<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ExclusivePostMedia implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(private readonly string $otherField)
    {
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $other = $this->data[$this->otherField] ?? null;

        if (! $this->hasMedia($value) || ! $this->hasMedia($other)) {
            return;
        }

        $fail('Choose either images or a video.');
    }

    private function hasMedia(mixed $value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value)) > 0;
        }

        return ! is_null($value);
    }
}

