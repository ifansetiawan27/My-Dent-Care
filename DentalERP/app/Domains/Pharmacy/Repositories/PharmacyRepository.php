<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Repositories;

use App\Domains\Pharmacy\Interfaces\PharmacyRepositoryInterface;
use App\Domains\Pharmacy\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PharmacyRepository implements PharmacyRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Pharmacy::where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($filters['expiry_date'])) {
            $query->whereDate('expiry_date', $filters['expiry_date']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('drug_code', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'drug_code', 'expiry_date'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Pharmacy
    {
        return Pharmacy::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Pharmacy
    {
        return Pharmacy::create($data);
    }

    public function update(Pharmacy $pharmacy, array $data): Pharmacy
    {
        $pharmacy->update($data);
        return $pharmacy->refresh();
    }

    public function delete(Pharmacy $pharmacy): bool
    {
        return (bool) $pharmacy->delete();
    }

    public function existsByDrugCode(string $drugCode, string $organizationId, ?string $excludeId = null): bool
    {
        $query = Pharmacy::where('drug_code', $drugCode)
            ->where('organization_id', $organizationId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}