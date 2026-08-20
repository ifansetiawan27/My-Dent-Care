<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Resources;

use App\Domains\Reporting\Enums\ReportStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Reporting\Models\Reporting */
final class ReportingResource extends JsonResource
{
    /**
     * The payload exposes a business field named `data`, which makes Laravel
     * treat the resource as already wrapped and skip the `data` envelope.
     * Forcing wrapping keeps the response aligned with docs/Reporting/API.md.
     */
    public static bool $forceWrapping = true;

    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'report_type'      => $this->report_type,
            'name'             => $this->name,
            'parameters'       => $this->parameters,
            'data'             => $this->data,
            'status'           => $this->status,
            'status_label'     => ReportStatus::from($this->status)->label(),
            'report_date'      => $this->report_date?->format('Y-m-d'),
            'created_by'       => $this->created_by,
            'updated_by'       => $this->updated_by,
            'deleted_by'       => $this->deleted_by,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
            'deleted_at'       => $this->deleted_at?->toISOString(),
        ];
    }
}