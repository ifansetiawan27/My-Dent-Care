<?php
declare(strict_types=1);
namespace App\Domains\MasterData\Repositories;
use App\Domains\MasterData\Models\AppointmentStatus;
class AppointmentStatusRepository extends BaseMasterDataRepository { public function __construct(AppointmentStatus $m) { parent::__construct($m); } }
