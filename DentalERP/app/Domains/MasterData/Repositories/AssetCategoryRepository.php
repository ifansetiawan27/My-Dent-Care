<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\AssetCategory;
class AssetCategoryRepository extends BaseMasterDataRepository { public function __construct(AssetCategory $m) { parent::__construct($m); } }
