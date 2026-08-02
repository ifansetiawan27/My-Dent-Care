<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\LanguageRepository;

/**
 * LanguageService
 *
 * Business operations for the languages reference table.
 */
class LanguageService extends BaseMasterDataService
{
    public function __construct(LanguageRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'LanguageService';
    }
}
