<?php

namespace App\Http\Requests\Posts;

use App\Models\Post;
use App\Models\User;
use App\Rules\ExclusivePostMedia;
use App\Rules\PollDurationRequired;
use App\Rules\ValidPostMedia;
use App\Rules\ValidPollOptions;
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
            'media' => ['array', new ValidPostMedia, new ExclusivePostMedia('poll_options')],
            'poll_options' => ['array', 'max:4', new ExclusivePostMedia('media'), new ValidPollOptions],
            'poll_options.*' => ['nullable', 'string', 'max:50'],
            'poll_duration' => ['nullable', 'integer', Rule::in([1440, 4320, 10080]), new PollDurationRequired],
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
