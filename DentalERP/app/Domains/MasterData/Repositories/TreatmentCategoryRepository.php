<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\TreatmentCategory;
class TreatmentCategoryRepository extends BaseMasterDataRepository { public function __construct(TreatmentCategory $m) { parent::__construct($m); } }
