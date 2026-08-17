<?php

declare(strict_types=1);

namespace App\Domains\Billing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Billing\Models\Billing */
final class BillingResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'patient_id'       => $this->patient_id,
            'invoice_number'   => $this->invoice_number,
            'total_amount'     => $this->total_amount,
            'paid_amount'      => $this->paid_amount,
            'status'           => $this->status,
            'due_date'         => $this->due_date?->toDateString(),
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