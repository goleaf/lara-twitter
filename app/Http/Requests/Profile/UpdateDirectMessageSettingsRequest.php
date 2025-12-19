<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDirectMessageSettingsRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'dm_policy' => ['required', 'string', Rule::in(User::dmPolicies())],
            'dm_allow_requests' => ['required', 'boolean'],
            'dm_read_receipts' => ['required', 'boolean'],
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
