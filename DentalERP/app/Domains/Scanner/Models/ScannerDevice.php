<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Models;
use App\Core\Base\BaseModel;
class ScannerDevice extends BaseModel {
    protected $table = 'scanner_devices';
    protected $casts = [
        'status' => 'string',
        'last_calibration_at' => 'datetime',
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
