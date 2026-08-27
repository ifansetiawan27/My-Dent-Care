<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Models;

use App\Core\Base\BaseModel;

class RadiologyOrder extends BaseModel
{
    protected $table = 'radiology_orders';

    protected $casts = [
        'priority'     => 'string',
        'status'       => 'string',
        'ordered_at'   => 'datetime',
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(RadiologyImage::class, 'radiology_order_id');
    }

    public function report()
    {
        return $this->hasOne(RadiologyReport::class, 'radiology_order_id');
    }
}
