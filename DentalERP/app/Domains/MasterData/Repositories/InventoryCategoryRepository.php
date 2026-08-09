<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\InventoryCategory;
class InventoryCategoryRepository extends BaseMasterDataRepository { public function __construct(InventoryCategory $m) { parent::__construct($m); } }
