<?php

declare(strict_types=1);

namespace App\Domains\Asset\Resources;

use App\Domains\Asset\Enums\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Asset\Models\Asset */
final class AssetResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'branch_id'        => $this->branch_id,
            'category_id'      => $this->category_id,
            'asset_code'       => $this->asset_code,
            'name'             => $this->name,
            'description'      => $this->description,
            'purchase_date'    => $this->purchase_date?->format('Y-m-d'),
            'purchase_price'   => $this->purchase_price,
            'status'           => $this->status,
            'status_label'     => AssetStatus::from($this->status)->label(),
            'warranty_expiry'  => $this->warranty_expiry?->format('Y-m-d'),
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