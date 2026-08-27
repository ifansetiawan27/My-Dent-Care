<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Models;
use App\Core\Base\BaseModel;
class ScanSession extends BaseModel {
    protected $table = 'scan_sessions';
    protected $casts = [
        'scan_type' => 'string',
        'status' => 'string',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function device() { return $this->belongsTo(ScannerDevice::class, 'device_id'); }
    public function scanFiles() { return $this->hasMany(ScanFile::class, 'scan_session_id'); }
}
