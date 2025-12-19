<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileInformationRequest extends FormRequest
{
    public static function rulesFor(User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'lowercase',
                'alpha_dash',
                'min:3',
                'max:30',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_date_visibility' => ['nullable', Rule::in(User::birthDateVisibilities())],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'header' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesFor($this->user());
    }
}
