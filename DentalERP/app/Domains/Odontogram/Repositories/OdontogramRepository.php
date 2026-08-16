<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Repositories;

use App\Domains\Odontogram\Interfaces\OdontogramRepositoryInterface;
use App\Domains\Odontogram\Models\Odontogram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class OdontogramRepository implements OdontogramRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Odontogram::where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['tooth_number'])) {
            $query->where('tooth_number', $filters['tooth_number']);
        }

        return $query->orderBy('tooth_number')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Odontogram
    {
        return Odontogram::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Odontogram
    {
        return Odontogram::create($data);
    }

    public function update(Odontogram $odontogram, array $data): Odontogram
    {
        $odontogram->update($data);
        return $odontogram->refresh();
    }

    public function delete(Odontogram $odontogram): bool
    {
        return (bool) $odontogram->delete();
    }

    public function existsByCode(string $code, ?string $excludeId = null): bool
    {
        $query = Odontogram::where('tooth_number', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}