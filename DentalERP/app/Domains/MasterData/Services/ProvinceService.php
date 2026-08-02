<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\ProvinceRepository;
use Illuminate\Support\Collection;

/**
 * ProvinceService
 *
 * Business operations for the provinces reference table.
 * Adds country-scoped lookup for cascading dropdowns.
 */
class ProvinceService extends BaseMasterDataService
{
    public function __construct(ProvinceRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'ProvinceService';
    }

    /**
     * Get active provinces belonging to a country.
     *
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function getByCountry(string $countryId): Collection
    {
        /** @var ProvinceRepository $repo */
        $repo = $this->repository;

        return $repo->findByParent($countryId);
    }
}
