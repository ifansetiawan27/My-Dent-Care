<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\TreatmentCategoryRepository;
class TreatmentCategoryService extends BaseMasterDataService { public function __construct(TreatmentCategoryRepository $r) { parent::__construct($r); $this->serviceName = 'TreatmentCategoryService'; } }
