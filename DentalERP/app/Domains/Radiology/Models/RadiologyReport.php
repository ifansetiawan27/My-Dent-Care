<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Models;

use App\Core\Base\BaseModel;

class RadiologyReport extends BaseModel
{
    protected $table = 'radiology_reports';

    protected $casts = [
        'is_final'    => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function radiologyOrder()
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }
}
