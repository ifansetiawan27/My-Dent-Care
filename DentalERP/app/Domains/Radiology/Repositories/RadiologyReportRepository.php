<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Repositories;

use App\Domains\Radiology\Interfaces\RadiologyReportRepositoryInterface;
use App\Domains\Radiology\Models\RadiologyReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RadiologyReportRepository implements RadiologyReportRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = RadiologyReport::query();

        if (! empty($filters['radiology_order_id'])) {
            $query->where('radiology_order_id', $filters['radiology_order_id']);
        }

        // Join with orders to filter by organization
        $query->join('radiology_orders', 'radiology_reports.radiology_order_id', '=', 'radiology_orders.id')
            ->where('radiology_orders.organization_id', $filters['organization_id'])
            ->select('radiology_reports.*');

        if (! empty($filters['is_final'])) {
            $query->where('is_final', $filters['is_final']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'reviewed_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy('radiology_reports.' . $sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?RadiologyReport
    {
        return RadiologyReport::join('radiology_orders', 'radiology_reports.radiology_order_id', '=', 'radiology_orders.id')
            ->where('radiology_reports.id', $id)
            ->where('radiology_orders.organization_id', $organizationId)
            ->select('radiology_reports.*')
            ->first();
    }

    public function create(array $data): RadiologyReport
    {
        return RadiologyReport::create($data);
    }

    public function update(RadiologyReport $report, array $data): RadiologyReport
    {
        $report->update($data);
        return $report->refresh();
    }

    public function delete(RadiologyReport $report): bool
    {
        return (bool) $report->delete();
    }
}
