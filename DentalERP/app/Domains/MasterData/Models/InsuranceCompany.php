<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * InsuranceCompany
 *
 * Insurance company reference (BPJS Kesehatan, Prudential, AXA, etc.).
 *
 * @property string      $type
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $claim_procedure
 */
class InsuranceCompany extends BaseMasterDataModel
{
    protected $table = 'insurance_companies';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'phone',
        'email',
        'website',
        'claim_procedure',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
