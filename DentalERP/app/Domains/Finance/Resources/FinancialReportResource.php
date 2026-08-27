<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Finance\Models\FinancialReport */
final class FinancialReportResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'             => $this->id,
            'report_type'    => $this->report_type,
            'report_name'    => $this->report_name,
            'period_start'   => $this->period_start?->toDateString(),
            'period_end'     => $this->period_end?->toDateString(),
            'filters'        => $this->filters,
            'status'         => $this->status,
            'export_format'  => $this->export_format,
            'generated_by'   => $this->generated_by,
            'generated_at'   => $this->generated_at?->toISOString(),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
