<?php

namespace App\Http\Requests\Posts;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class QuoteRepostRequest extends FormRequest
{
    public static function rulesFor(?User $user = null): array
    {
        $max = ($user?->is_premium ?? false) ? 25000 : 280;

        return [
            'quote_body' => ['required', 'string', "max:{$max}"],
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
