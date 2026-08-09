<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use App\Domains\MasterData\Models\BaseMasterDataModel;

class InventoryCategory extends BaseMasterDataModel
{
    protected $table = 'inventory_categories';
}
