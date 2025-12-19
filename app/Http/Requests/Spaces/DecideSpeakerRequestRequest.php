<?php

namespace App\Http\Requests\Spaces;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideSpeakerRequestRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'request_id' => ['required', 'integer', 'exists:space_speaker_requests,id'],
            'decision' => ['required', 'string', Rule::in(['approve', 'deny'])],
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

