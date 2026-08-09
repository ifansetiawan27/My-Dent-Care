<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Services;
use App\Domains\MasterData\Repositories\AppointmentStatusRepository;
class AppointmentStatusService extends BaseMasterDataService { public function __construct(AppointmentStatusRepository $r) { parent::__construct($r); $this->serviceName = 'AppointmentStatusService'; } }
