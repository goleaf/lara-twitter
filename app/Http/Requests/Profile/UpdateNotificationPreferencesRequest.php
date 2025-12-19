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
            'followed_posts' => ['boolean'],
            'email_enabled' => ['boolean'],
            'quiet_hours_enabled' => ['boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
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
