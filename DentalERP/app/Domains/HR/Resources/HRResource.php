<?php

declare(strict_types=1);

namespace App\Domains\HR\Resources;

use App\Domains\HR\Enums\HRStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\HR\Models\HR */
final class HRResource extends JsonResource
{
    /**
     * The payload exposes a business field named `data`, which makes Laravel
     * treat the resource as already wrapped and skip the `data` envelope.
     * Forcing wrapping keeps the response aligned with docs/HR/API.md.
     */
    public static bool $forceWrapping = true;

    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'employee_id'      => $this->employee_id,
            'record_type'      => $this->record_type,
            'status'           => $this->status,
            'status_label'     => HRStatus::from($this->status)->label(),
            'effective_date'   => $this->effective_date?->format('Y-m-d'),
            'end_date'         => $this->end_date?->format('Y-m-d'),
            'data'             => $this->data,
            'notes'            => $this->notes,
            'created_by'       => $this->created_by,
            'updated_by'       => $this->updated_by,
            'deleted_by'       => $this->deleted_by,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
            'deleted_at'       => $this->deleted_at?->toISOString(),
        ];
    }
}