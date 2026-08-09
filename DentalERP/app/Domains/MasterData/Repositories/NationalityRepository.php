<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\Nationality;
class NationalityRepository extends BaseMasterDataRepository { public function __construct(Nationality $m) { parent::__construct($m); } }
