<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['array', 'max:4'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,audio/mpeg,audio/mp4,audio/wav,audio/webm',
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

