<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:80'],
            'recipientUserIds' => ['required', 'array', 'min:1', 'max:49'],
            'recipientUserIds.*' => ['integer', 'distinct', 'exists:users,id'],
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
