<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Radiology\Models\RadiologyImage */
final class RadiologyImageResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'                 => $this->id,
            'radiology_order_id' => $this->radiology_order_id,
            'image_type'         => $this->image_type,
            'file_path'          => $this->file_path,
            'file_size'          => $this->file_size,
            'file_mime'          => $this->file_mime,
            'thumbnail_path'     => $this->thumbnail_path,
            'uploaded_by'        => $this->uploaded_by,
            'created_by'         => $this->created_by,
            'updated_by'         => $this->updated_by,
            'deleted_by'         => $this->deleted_by,
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
            'deleted_at'         => $this->deleted_at?->toISOString(),
        ];
    }
}
