<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class AddConversationMemberRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'memberUsername' => ['required', 'string', 'max:50'],
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
