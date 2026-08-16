<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use App\Domains\Branch\Resources\BranchResource;
use App\Domains\Organization\Resources\OrganizationResource;
use Illuminate\Http\Request;

class ProfileResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'] ?? null;

        if ($user === null) {
            return [];
        }

        return [
            'id'              => $user->id,
            'employee_code'   => $user->employee_code,
            'name'            => $user->name,
            'username'        => $user->username,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'photo'           => $this->resource['photo_url'] ?? $user->photo,
            'gender'          => $user->gender?->value,
            'gender_label'    => $user->gender?->label(),
            'birth_date'      => $user->birth_date?->toDateString(),
            'organization_id' => $user->organization_id,
            'branch_id'       => $user->branch_id,
            'status'          => $user->status->value,
            'status_label'    => $user->status->label(),
            'organization'    => $this->when(
                isset($this->resource['organization']),
                fn () => new OrganizationResource($this->resource['organization']),
            ),
            'branch'          => $this->when(
                isset($this->resource['branch']),
                fn () => new BranchResource($this->resource['branch']),
            ),
            'roles'       => $this->resource['roles'] ?? [],
            'permissions' => $this->resource['permissions'] ?? [],
        ];
    }
}
