<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * TaxRate
 *
 * Tax rate reference (PPN 11%, PPh 21, etc.).
 * Rate is stored as a percentage value (e.g. 11.00 = 11%).
 *
 * @property string      $rate
 * @property string|null $description
 */
class TaxRate extends BaseMasterDataModel
{
    protected $table = 'tax_rates';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'rate',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active'  => 'boolean',
        'rate'       => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
