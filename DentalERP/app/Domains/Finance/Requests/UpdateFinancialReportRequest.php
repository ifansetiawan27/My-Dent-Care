<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Domains\Finance\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFinancialReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type'   => ['sometimes', 'string', Rule::in(ReportType::values())],
            'report_name'   => 'sometimes|string',
            'period_start'  => 'sometimes|date',
            'period_end'    => 'sometimes|date|after_or_equal:period_start',
            'filters'       => 'nullable|array',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ];
    }
}
