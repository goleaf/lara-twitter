<?php

namespace App\Http\Requests\Lists;

use App\Models\UserList;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreListRequest extends FormRequest
{
    public static function rulesFor(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:80',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! Auth::check()) {
                        return;
                    }

                    $count = Auth::user()->listsOwned()->count();
                    if ($count >= UserList::MAX_LISTS_PER_OWNER) {
                        $fail('You can create up to '.UserList::MAX_LISTS_PER_OWNER.' lists.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:160'],
            'is_private' => ['boolean'],
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
