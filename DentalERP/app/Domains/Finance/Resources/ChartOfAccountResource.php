<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Finance\Models\ChartOfAccount */
final class ChartOfAccountResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'account_code'     => $this->account_code,
            'account_name'     => $this->account_name,
            'account_type'     => $this->account_type,
            'account_category' => $this->account_category,
            'parent_id'        => $this->parent_id,
            'parent'           => $this->whenLoaded('parent', fn () => [
                'id'           => $this->parent->id,
                'account_code' => $this->parent->account_code,
                'account_name' => $this->parent->account_name,
            ]),
            'is_active'        => $this->is_active,
            'is_system'        => $this->is_system,
            'description'      => $this->description,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
