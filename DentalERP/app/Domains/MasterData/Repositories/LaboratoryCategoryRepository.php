<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\LaboratoryCategory;
class LaboratoryCategoryRepository extends BaseMasterDataRepository { public function __construct(LaboratoryCategory $m) { parent::__construct($m); } }
