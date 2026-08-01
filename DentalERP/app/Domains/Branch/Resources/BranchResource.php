<?php

declare(strict_types=1);

namespace App\Domains\Branch\Resources;

use App\Core\Base\BaseResource;
use App\Domains\Organization\Resources\OrganizationResource;
use Illuminate\Http\Request;

/**
 * BranchResource
 *
 * Transforms a Branch model into a standardized API response array.
 * The `organization` relation is conditionally included when eager-loaded.
 *
 * @mixin \App\Domains\Branch\Models\Branch
 */
class BranchResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ---------------------------------------------------------------
            // Identity
            // ---------------------------------------------------------------
            'id'              => $this->id,
            'organization_id' => $this->organization_id,
            'branch_code'     => $this->branch_code,
            'branch_name'     => $this->branch_name,
            'branch_type'     => $this->branch_type,

            // ---------------------------------------------------------------
            // Relationship — only included when eager-loaded
            // Usage: Branch::with('organization')->get()
            // ---------------------------------------------------------------
            'organization' => $this->whenLoaded(
                'organization',
                fn () => new OrganizationResource($this->organization),
            ),

            // ---------------------------------------------------------------
            // Contact
            // ---------------------------------------------------------------
            'email' => $this->email,
            'phone' => $this->phone,

            // ---------------------------------------------------------------
            // Address
            // ---------------------------------------------------------------
            'address'     => $this->address,
            'city'        => $this->city,
            'province'    => $this->province,
            'country'     => $this->country,
            'postal_code' => $this->postal_code,

            // ---------------------------------------------------------------
            // Geolocation
            // ---------------------------------------------------------------
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,

            // ---------------------------------------------------------------
            // Locale
            // ---------------------------------------------------------------
            'timezone' => $this->timezone,

            // ---------------------------------------------------------------
            // Status
            // ---------------------------------------------------------------
            'status'       => $this->status->value,
            'status_label' => $this->status->label(),

            // ---------------------------------------------------------------
            // Audit — created_at, updated_at, created_by, updated_by
            // ---------------------------------------------------------------
            ...$this->auditFields(),
        ];
    }
}
