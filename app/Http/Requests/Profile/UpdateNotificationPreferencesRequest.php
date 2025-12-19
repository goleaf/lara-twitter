<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'likes' => ['boolean'],
            'reposts' => ['boolean'],
            'replies' => ['boolean'],
            'mentions' => ['boolean'],
            'follows' => ['boolean'],
            'dms' => ['boolean'],
            'quality_filter' => ['boolean'],
            'only_following' => ['boolean'],
            'only_verified' => ['boolean'],
            'lists' => ['boolean'],
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
