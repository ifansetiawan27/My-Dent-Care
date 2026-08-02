<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * PatientType
 *
 * Patient classification reference (General, BPJS, Insurance, VIP, etc.).
 *
 * @property string|null $description
 */
class PatientType extends BaseMasterDataModel
{
    protected $table = 'patient_types';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
