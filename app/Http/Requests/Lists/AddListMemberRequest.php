<?php

namespace App\Http\Requests\Lists;

use Illuminate\Foundation\Http\FormRequest;

class AddListMemberRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'member_username' => ['required', 'string', 'max:31', 'regex:/^@?[A-Za-z0-9_-]{1,30}$/'],
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

