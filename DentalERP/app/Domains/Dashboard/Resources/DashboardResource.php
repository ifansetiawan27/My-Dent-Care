<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Dashboard\Models\Dashboard */
final class DashboardResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'user_id'          => $this->user_id,
            'name'             => $this->name,
            'config'           => $this->config,
            'widgets'          => $this->widgets,
            'is_default'       => $this->is_default,
            'created_by'       => $this->created_by,
            'updated_by'       => $this->updated_by,
            'deleted_by'       => $this->deleted_by,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
            'deleted_at'       => $this->deleted_at?->toISOString(),
        ];
    }
}