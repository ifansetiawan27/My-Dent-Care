<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Models;

use App\Core\Base\BaseModel;

class Appointment extends BaseModel
{
    protected $table = 'appointments';

    protected $casts = [
        'scheduled_at' => 'datetime',
        'end_at'       => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Domains\Patient\Models\Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Domains\Doctor\Models\Doctor::class);
    }
}