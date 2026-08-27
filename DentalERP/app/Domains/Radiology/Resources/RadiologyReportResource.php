<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Radiology\Models\RadiologyReport */
final class RadiologyReportResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'                 => $this->id,
            'radiology_order_id' => $this->radiology_order_id,
            'radiologist_id'     => $this->radiologist_id,
            'findings'           => $this->findings,
            'impression'         => $this->impression,
            'diagnosis'          => $this->diagnosis,
            'is_final'           => $this->is_final,
            'reviewed_at'        => $this->reviewed_at?->toISOString(),
            'created_by'         => $this->created_by,
            'updated_by'         => $this->updated_by,
            'deleted_by'         => $this->deleted_by,
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
            'deleted_at'         => $this->deleted_at?->toISOString(),
        ];
    }
}
