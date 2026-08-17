<?php

declare(strict_types=1);

namespace App\Domains\CRM\Resources;

use App\Domains\CRM\Enums\CRMStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\CRM\Models\CRM */
final class CRMResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'               => $this->id,
            'organization_id'  => $this->organization_id,
            'patient_id'       => $this->patient_id,
            'contact_type'     => $this->contact_type,
            'channel'          => $this->channel,
            'subject'          => $this->subject,
            'message'          => $this->message,
            'status'           => $this->status,
            'status_label'     => CRMStatus::from($this->status)->label(),
            'follow_up_date'   => $this->follow_up_date?->format('Y-m-d'),
            'resolution'       => $this->resolution,
            'created_by'       => $this->created_by,
            'updated_by'       => $this->updated_by,
            'deleted_by'       => $this->deleted_by,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
            'deleted_at'       => $this->deleted_at?->toISOString(),
        ];
    }
}