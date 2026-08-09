<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\AssetCategoryRepository;
class AssetCategoryService extends BaseMasterDataService { public function __construct(AssetCategoryRepository $r) { parent::__construct($r); $this->serviceName = 'AssetCategoryService'; } }
