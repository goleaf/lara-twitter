<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalyticsSettingsRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'analytics_enabled' => ['boolean'],
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

