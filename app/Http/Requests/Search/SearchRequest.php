<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'string', Rule::in(['all', 'posts', 'users', 'tags'])],
            'user' => ['nullable', 'string', 'max:31', 'regex:/^@?[A-Za-z0-9_-]{1,30}$/'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesFor();
    }
}
