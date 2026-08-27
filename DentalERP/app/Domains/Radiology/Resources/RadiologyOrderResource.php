<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Radiology\Models\RadiologyOrder */
final class RadiologyOrderResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'             => $this->id,
            'organization_id' => $this->organization_id,
            'patient_id'     => $this->patient_id,
            'doctor_id'      => $this->doctor_id,
            'order_number'   => $this->order_number,
            'radiology_type' => $this->radiology_type,
            'body_part'      => $this->body_part,
            'clinical_notes' => $this->clinical_notes,
            'priority'       => $this->priority,
            'status'         => $this->status,
            'ordered_at'     => $this->ordered_at?->toISOString(),
            'completed_at'   => $this->completed_at?->toISOString(),
            'created_by'     => $this->created_by,
            'updated_by'     => $this->updated_by,
            'deleted_by'     => $this->deleted_by,
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
            'deleted_at'     => $this->deleted_at?->toISOString(),
        ];
    }
}
