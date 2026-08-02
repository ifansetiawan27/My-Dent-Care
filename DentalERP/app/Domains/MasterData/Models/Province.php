<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Province
 *
 * Province / state reference. Belongs to a Country.
 *
 * @property string $country_id
 */
class Province extends BaseMasterDataModel
{
    protected $table = 'provinces';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'country_id',
        'code',
        'name',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * @return BelongsTo<Country, Province>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * @return HasMany<City>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id', 'id');
    }
}
