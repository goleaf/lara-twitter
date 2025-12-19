<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnblockUserRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'blocked_id' => [
                'required',
                'integer',
                Rule::exists('blocks', 'blocked_id')->where(fn ($q) => $q->where('blocker_id', Auth::id())),
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

