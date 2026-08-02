<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Currency
 *
 * ISO 4217 currency reference.
 *
 * @property string $symbol
 * @property int    $decimal_places
 */
class Currency extends BaseMasterDataModel
{
    protected $table = 'currencies';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active'      => 'boolean',
        'decimal_places' => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}
