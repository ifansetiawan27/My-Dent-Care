<?php

declare(strict_types=1);

namespace App\Domains\EMR\Models;

use App\Core\Base\BaseModel;

class EMR extends BaseModel
{
    protected $table = 'emrs';

    protected $casts = [
        'vital_signs'      => 'array',
        'examination_date' => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Domains\Patient\Models\Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Domains\Doctor\Models\Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(\App\Domains\Appointment\Models\Appointment::class);
    }
}