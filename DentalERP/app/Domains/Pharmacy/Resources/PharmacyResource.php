<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Pharmacy\Models\Pharmacy */
final class PharmacyResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'branch_id'        => $this->branch_id,
            'drug_code'        => $this->drug_code,
            'name'             => $this->name,
            'category'         => $this->category,
            'quantity'         => $this->quantity,
            'unit'             => $this->unit,
            'unit_price'       => $this->unit_price,
            'expiry_date'      => $this->expiry_date?->format('Y-m-d'),
            'batch_number'     => $this->batch_number,
            'is_active'        => $this->is_active,
            'created_by'       => $this->created_by,
            'updated_by'       => $this->updated_by,
            'deleted_by'       => $this->deleted_by,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
            'deleted_at'       => $this->deleted_at?->toISOString(),
        ];
    }
}