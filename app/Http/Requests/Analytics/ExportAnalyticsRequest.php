<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class ExportAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && ($user->analytics_enabled || $user->is_admin);
    }

    public function rules(): array
    {
        return [
            'range' => ['nullable', 'in:7d,28d,90d'],
        ];
    }
}
