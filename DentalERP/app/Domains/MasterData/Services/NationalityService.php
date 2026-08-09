<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\NationalityRepository;
class NationalityService extends BaseMasterDataService { public function __construct(NationalityRepository $r) { parent::__construct($r); $this->serviceName = 'NationalityService'; } }
