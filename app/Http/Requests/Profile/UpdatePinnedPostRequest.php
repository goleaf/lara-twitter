<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePinnedPostRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'pinned_post_id' => ['nullable', 'integer', 'exists:posts,id'],
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

