<?php

namespace App\Http\Requests\Lists;

use Illuminate\Foundation\Http\FormRequest;

class StoreListRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:160'],
            'is_private' => ['boolean'],
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

