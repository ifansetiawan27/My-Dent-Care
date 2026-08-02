<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\DistrictRepository;
use Illuminate\Support\Collection;

/**
 * DistrictService
 *
 * Business operations for the districts reference table.
 * Adds city-scoped lookup for cascading dropdowns.
 */
class DistrictService extends BaseMasterDataService
{
    public function __construct(DistrictRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'DistrictService';
    }

    /**
     * Get active districts belonging to a city.
     *
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function getByCity(string $cityId): Collection
    {
        /** @var DistrictRepository $repo */
        $repo = $this->repository;

        return $repo->findByParent($cityId);
    }
}
