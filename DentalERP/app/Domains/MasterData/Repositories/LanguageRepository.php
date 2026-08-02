<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Language;

/**
 * LanguageRepository
 *
 * Data access for the languages reference table.
 */
class LanguageRepository extends BaseMasterDataRepository
{
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }
}
