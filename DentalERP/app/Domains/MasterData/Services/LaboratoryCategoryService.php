<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\LaboratoryCategoryRepository;
class LaboratoryCategoryService extends BaseMasterDataService { public function __construct(LaboratoryCategoryRepository $r) { parent::__construct($r); $this->serviceName = 'LaboratoryCategoryService'; } }
