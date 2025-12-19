<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMutedTermRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'term' => ['required', 'string', 'max:100'],
            'duration' => ['required', 'string', Rule::in(['forever', '1h', '1d', '7d', '30d'])],
            'whole_word' => ['boolean'],
            'only_non_followed' => ['boolean'],
            'mute_timeline' => ['boolean'],
            'mute_notifications' => ['boolean'],
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

