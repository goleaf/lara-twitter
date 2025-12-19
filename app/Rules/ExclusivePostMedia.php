<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class ExclusivePostMedia implements DataAwareRule, Rule
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

    public function passes($attribute, $value): bool
    {
        $other = $this->data[$this->otherField] ?? null;

        return ! ($this->hasMedia($value) && $this->hasMedia($other));
    }

    public function message(): string
    {
        return 'Choose only one attachment type.';
    }

    private function hasMedia(mixed $value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value)) > 0;
        }

        return ! is_null($value);
    }
}
