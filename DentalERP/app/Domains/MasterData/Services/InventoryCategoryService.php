<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\InventoryCategoryRepository;
class InventoryCategoryService extends BaseMasterDataService { public function __construct(InventoryCategoryRepository $r) { parent::__construct($r); $this->serviceName = 'InventoryCategoryService'; } }
