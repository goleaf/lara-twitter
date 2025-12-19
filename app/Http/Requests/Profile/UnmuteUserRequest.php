<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnmuteUserRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'muted_id' => [
                'required',
                'integer',
                Rule::exists('mutes', 'muted_id')->where(fn ($q) => $q->where('muter_id', Auth::id())),
            ],
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

