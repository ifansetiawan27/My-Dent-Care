<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Inventory\Models\Inventory */
final class InventoryResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'branch_id'        => $this->branch_id,
            'category_id'      => $this->category_id,
            'item_code'        => $this->item_code,
            'name'             => $this->name,
            'description'      => $this->description,
            'unit'             => $this->unit,
            'quantity'         => $this->quantity,
            'min_quantity'     => $this->min_quantity,
            'unit_price'       => $this->unit_price,
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