<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * UserSummaryResource
 *
 * Transforms a User model into a condensed authentication response
 * payload. Excludes sensitive fields — passwords and tokens are
 * never exposed. Designed for embedding inside LoginResource.
 *
 * @mixin \App\Domains\User\Models\User
 */
class UserSummaryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'employee_code'   => $this->employee_code,
            'name'            => $this->name,
            'username'        => $this->username,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'photo'           => $this->photo,
            'gender'          => $this->gender?->value,
            'gender_label'    => $this->gender?->label(),
            'birth_date'      => $this->birth_date?->toDateString(),
            'organization_id' => $this->organization_id,
            'branch_id'       => $this->branch_id,
            'status'          => $this->status->value,
            'status_label'    => $this->status->label(),
        ];
    }
}
