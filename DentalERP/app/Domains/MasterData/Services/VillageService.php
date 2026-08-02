<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\VillageRepository;
use Illuminate\Support\Collection;

/**
 * VillageService
 *
 * Business operations for the villages reference table.
 * Adds district-scoped lookup for cascading dropdowns.
 */
class VillageService extends BaseMasterDataService
{
    public function __construct(VillageRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'VillageService';
    }

    /**
     * Get active villages belonging to a district.
     *
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function getByDistrict(string $districtId): Collection
    {
        /** @var VillageRepository $repo */
        $repo = $this->repository;

        return $repo->findByParent($districtId);
    }
}
