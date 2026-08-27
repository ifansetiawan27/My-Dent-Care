<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Models;

use App\Core\Base\BaseModel;

class RadiologyImage extends BaseModel
{
    protected $table = 'radiology_images';

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function radiologyOrder()
    {
        return $this->belongsTo(RadiologyOrder::class, 'radiology_order_id');
    }
}
