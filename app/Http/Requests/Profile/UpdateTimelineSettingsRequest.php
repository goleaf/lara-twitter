<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimelineSettingsRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'show_replies' => ['required', 'boolean'],
            'show_retweets' => ['required', 'boolean'],
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

