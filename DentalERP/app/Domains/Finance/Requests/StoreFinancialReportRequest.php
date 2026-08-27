<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use App\Domains\Finance\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreFinancialReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type'   => ['required', 'string', Rule::in(ReportType::values())],
            'report_name'   => 'required|string',
            'period_start'  => 'required|date',
            'period_end'    => 'required|date|after_or_equal:period_start',
            'filters'       => 'nullable|array',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ];
    }
}
