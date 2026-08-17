<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Treatment\Models\Treatment */
final class TreatmentResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'              => $this->id,
            'organization_id' => $this->organization_id,
            'patient_id'      => $this->patient_id,
            'doctor_id'       => $this->doctor_id,
            'appointment_id'  => $this->appointment_id,
            'treatment_type'  => $this->treatment_type,
            'status'          => $this->status,
            'cost'            => $this->cost,
            'description'     => $this->description,
            'procedure_data'  => $this->procedure_data,
            'created_by'      => $this->created_by,
            'updated_by'      => $this->updated_by,
            'deleted_by'      => $this->deleted_by,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
            'deleted_at'      => $this->deleted_at?->toISOString(),
        ];
    }
}