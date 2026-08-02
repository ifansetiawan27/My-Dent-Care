<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\CityRepository;
use Illuminate\Support\Collection;

/**
 * CityService
 *
 * Business operations for the cities reference table.
 * Adds province-scoped lookup for cascading dropdowns.
 */
class CityService extends BaseMasterDataService
{
    public function __construct(CityRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'CityService';
    }

    /**
     * Get active cities belonging to a province.
     *
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function getByProvince(string $provinceId): Collection
    {
        /** @var CityRepository $repo */
        $repo = $this->repository;

        return $repo->findByParent($provinceId);
    }
}
