<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Laboratory\Models\Laboratory */
final class LaboratoryResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'              => $this->id,
            'organization_id' => $this->organization_id,
            'patient_id'      => $this->patient_id,
            'doctor_id'       => $this->doctor_id,
            'order_number'    => $this->order_number,
            'category_id'     => $this->category_id,
            'status'          => $this->status,
            'description'     => $this->description,
            'results'         => $this->results,
            'ordered_at'      => $this->ordered_at?->format('Y-m-d'),
            'completed_at'    => $this->completed_at?->format('Y-m-d'),
            'notes'           => $this->notes,
            'created_by'      => $this->created_by,
            'updated_by'      => $this->updated_by,
            'deleted_by'      => $this->deleted_by,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
            'deleted_at'      => $this->deleted_at?->toISOString(),
        ];
    }
}