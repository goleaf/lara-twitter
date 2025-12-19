<?php

namespace App\Http\Requests\Lists;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'edit_name' => ['required', 'string', 'max:80'],
            'edit_description' => ['nullable', 'string', 'max:160'],
            'edit_is_private' => ['boolean'],
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

