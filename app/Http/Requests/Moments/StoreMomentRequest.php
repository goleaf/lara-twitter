<?php

namespace App\Http\Requests\Moments;

use Illuminate\Foundation\Http\FormRequest;

class StoreMomentRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:280'],
            'is_public' => ['boolean'],
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

