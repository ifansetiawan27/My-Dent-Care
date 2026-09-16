<?php

declare(strict_types=1);

namespace App\Domains\Patient\Models;

use App\Core\Base\BaseModel;
use App\Domains\MasterData\Models\PatientType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends BaseModel
{
    protected $table = 'patients';

    protected $casts = [
        'birth_date' => 'date',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Patient type (e.g. regular, VIP, BPJS). Read-side relation consumed by
     * PatientResource; eager-loaded by the repository to avoid N+1.
     */
    public function patientType(): BelongsTo
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }
}