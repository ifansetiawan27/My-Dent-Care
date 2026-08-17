<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Resources;

use App\Domains\Procurement\Enums\ProcurementStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Procurement\Models\Procurement */
final class ProcurementResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'branch_id'        => $this->branch_id,
            'supplier_id'      => $this->supplier_id,
            'order_number'     => $this->order_number,
            'status'           => $this->status,
            'status_label'     => ProcurementStatus::from($this->status)->label(),
            'order_date'       => $this->order_date?->format('Y-m-d'),
            'expected_date'    => $this->expected_date?->format('Y-m-d'),
            'total_amount'     => $this->total_amount,
            'items'            => $this->items,
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