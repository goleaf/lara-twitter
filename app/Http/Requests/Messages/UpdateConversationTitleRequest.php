<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConversationTitleRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'groupTitle' => ['nullable', 'string', 'max:80'],
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
