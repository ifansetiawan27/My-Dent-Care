<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Repositories;

use App\Domains\Radiology\Interfaces\RadiologyImageRepositoryInterface;
use App\Domains\Radiology\Models\RadiologyImage;
use App\Domains\Radiology\Models\RadiologyOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RadiologyImageRepository implements RadiologyImageRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = RadiologyImage::query();

        if (! empty($filters['radiology_order_id'])) {
            $query->where('radiology_order_id', $filters['radiology_order_id']);
        }

        // Join with orders to filter by organization
        $query->join('radiology_orders', 'radiology_images.radiology_order_id', '=', 'radiology_orders.id')
            ->where('radiology_orders.organization_id', $filters['organization_id'])
            ->select('radiology_images.*');

        if (! empty($filters['image_type'])) {
            $query->where('image_type', $filters['image_type']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy('radiology_images.' . $sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?RadiologyImage
    {
        return RadiologyImage::join('radiology_orders', 'radiology_images.radiology_order_id', '=', 'radiology_orders.id')
            ->where('radiology_images.id', $id)
            ->where('radiology_orders.organization_id', $organizationId)
            ->select('radiology_images.*')
            ->first();
    }

    public function create(array $data): RadiologyImage
    {
        return RadiologyImage::create($data);
    }

    public function update(RadiologyImage $image, array $data): RadiologyImage
    {
        $image->update($data);
        return $image->refresh();
    }

    public function delete(RadiologyImage $image): bool
    {
        return (bool) $image->delete();
    }
}
