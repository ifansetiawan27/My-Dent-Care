<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Country
 *
 * ISO 3166 country reference. Root of the geographic hierarchy.
 *
 * @property string|null $name_local
 * @property string|null $phone_code
 */
class Country extends BaseMasterDataModel
{
    protected $table = 'countries';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'name_local',
        'phone_code',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * A Country has many Provinces.
     *
     * @return HasMany<Province>
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id', 'id');
    }
}
