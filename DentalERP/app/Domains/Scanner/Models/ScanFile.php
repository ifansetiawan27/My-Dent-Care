<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Models;
use App\Core\Base\BaseModel;
class ScanFile extends BaseModel {
    protected $table = 'scan_files';
    protected $casts = [
        'file_size' => 'integer',
        'file_format' => 'string',
        'is_primary' => 'boolean',
        'processing_status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function scanSession() { return $this->belongsTo(ScanSession::class, 'scan_session_id'); }
}
