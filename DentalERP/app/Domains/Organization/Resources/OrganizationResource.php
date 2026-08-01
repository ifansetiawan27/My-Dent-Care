<?php

declare(strict_types=1);

namespace App\Domains\Organization\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin \App\Domains\Organization\Models\Organization
 */
class OrganizationResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Identity
            'id'          => $this->id,
            'code'        => $this->code,
            'name'        => $this->name,
            'legal_name'  => $this->legal_name,
            'tax_number'  => $this->tax_number,

            // Contact
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,

            // Media
            'logo'        => $this->logo
                ? asset('storage/' . $this->logo)
                : null,

            // Status
            'status'      => $this->status->value,
            'status_label'=> $this->status->label(),
            'is_active'   => $this->is_active,

            // Address
            'address'      => $this->address,
            'city'         => $this->city,
            'province'     => $this->province,
            'country'      => $this->country,
            'postal_code'  => $this->postal_code,
            'full_address' => $this->full_address,

            // Locale
            'timezone'    => $this->timezone,
            'currency'    => $this->currency,

            // Audit
            ...$this->auditFields(),
        ];
    }
}
