<?php

namespace App\Http\Requests\Links;

use App\Rules\SafeOutboundUrl;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LinkRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'u' => ['required', 'string', new SafeOutboundUrl()],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $status = $this->filled('u') ? 400 : 404;

        throw new HttpResponseException(response('', $status));
    }
}
