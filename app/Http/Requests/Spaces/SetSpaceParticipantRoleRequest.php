<?php

namespace App\Http\Requests\Spaces;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetSpaceParticipantRoleRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'participant_id' => ['required', 'integer', 'exists:space_participants,id'],
            'role' => ['required', 'string', Rule::in(['listener', 'speaker', 'cohost'])],
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

