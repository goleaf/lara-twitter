<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMutedTermRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:muted_terms,id'],
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

