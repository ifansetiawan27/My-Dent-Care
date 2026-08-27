<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\Models\RadiologyOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyOrderRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?RadiologyOrder;
    public function create(array $data): RadiologyOrder;
    public function update(RadiologyOrder $order, array $data): RadiologyOrder;
    public function delete(RadiologyOrder $order): bool;
}
