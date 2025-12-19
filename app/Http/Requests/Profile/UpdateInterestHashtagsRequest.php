<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInterestHashtagsRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'interest_hashtags' => ['nullable', 'string', 'max:400'],
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

