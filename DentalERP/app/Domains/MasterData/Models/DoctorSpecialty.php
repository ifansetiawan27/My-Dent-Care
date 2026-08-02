<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * DoctorSpecialty
 *
 * Doctor specialty reference (Orthodontist, Periodontist, etc.).
 *
 * @property string|null $description
 */
class DoctorSpecialty extends BaseMasterDataModel
{
    protected $table = 'doctor_specialties';

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
