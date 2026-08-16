<?php

declare(strict_types=1);

namespace App\Domains\EMR\Repositories;

use App\Domains\EMR\Interfaces\EMRRepositoryInterface;
use App\Domains\EMR\Models\EMR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EMRRepository implements EMRRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = EMR::with(['patient', 'doctor'])->where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?EMR
    {
        return EMR::with(['patient', 'doctor'])->where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): EMR
    {
        return EMR::create($data);
    }

    public function update(EMR $emr, array $data): EMR
    {
        $emr->update($data);
        return $emr->refresh()->load(['patient', 'doctor']);
    }

    public function delete(EMR $emr): bool
    {
        return (bool) $emr->delete();
    }

    public function existsByCode(string $code, ?string $excludeId = null): bool
    {
        $query = EMR::where('patient_id', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}