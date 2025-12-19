<?php

namespace App\Http\Requests\Posts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public static function rulesFor(?User $user = null): array
    {
        $max = ($user?->is_premium ?? false) ? 25000 : 280;

        return [
            'body' => ['required', 'string', "max:{$max}"],
            'reply_policy' => ['nullable', 'string', Rule::in(Post::replyPolicies())],
            'images' => ['array', 'max:4'],
            'images.*' => ['image', 'max:4096'],
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
