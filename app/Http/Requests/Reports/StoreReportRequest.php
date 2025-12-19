<?php

namespace App\Http\Requests\Reports;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function rulesFor(?string $reason = null): array
    {
        $detailsRequired = $reason !== null && in_array($reason, Report::reasonsRequiringDetails(), true);

        return [
            'reason' => ['required', 'string', Rule::in(Report::reasons())],
            'details' => [Rule::requiredIf($detailsRequired), 'nullable', 'string', 'max:1000'],
        ];
    }

    public function rules(): array
    {
        return self::rulesFor($this->input('reason'));
    }
}
