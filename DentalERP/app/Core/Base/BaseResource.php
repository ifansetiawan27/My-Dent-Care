<?php

declare(strict_types=1);

namespace App\Core\Base;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Override in child resources to define the response shape.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(Request $request): array;

    /**
     * Standard audit fields appended to every resource.
     * Call array_merge($this->fields(), $this->auditFields()) in child resources.
     *
     * @return array<string, mixed>
     */
    protected function auditFields(): array
    {
        return [
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
