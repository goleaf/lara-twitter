<?php

namespace App\Http\Requests\Moments;

use Illuminate\Foundation\Http\FormRequest;

class AddMomentItemRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'caption' => ['nullable', 'string', 'max:280'],
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
